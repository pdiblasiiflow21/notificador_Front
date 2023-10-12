<?php

declare(strict_types=1);

namespace Modules\NewSan\Tests\Unit\Services\V1;

use App\Exports\NewSanOrderInformedExport;
use App\Service\V1\IflowApiService;
use App\Service\V1\NewSanApiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Modules\NewSan\Entities\NewSanNotificationLog;
use Modules\NewSan\Entities\NewSanOrder;
use Modules\NewSan\Entities\NewSanOrderInformed;
use Modules\NewSan\Repositories\V1\NewSanNotificationLogRepository;
use Modules\NewSan\Repositories\V1\NewSanOrderInformedRepository;
use Modules\NewSan\Repositories\V1\NewSanOrderRepository;
use Modules\NewSan\Services\V1\NewSanService;
use Tests\TestCase;

/**
 * @coversNothing
 */
class NewSanServiceTest extends TestCase
{
    use RefreshDatabase;

    private $iflowApiServiceMock;

    private $newSanApiServiceMock;

    private $newSanOrderRepo;

    private $newSanOrderInformedRepo;

    private $newSanNotificationLogRepo;

    private $newSanService;

    public function setUp(): void
    {
        parent::setUp();

        // Mock API y repositories
        $this->iflowApiServiceMock       = $this->createMock(IflowApiService::class);
        $this->newSanApiServiceMock      = $this->createMock(NewSanApiService::class);
        $this->newSanOrderRepo           = app(NewSanOrderRepository::class);
        $this->newSanOrderInformedRepo   = app(NewSanOrderInformedRepository::class);
        $this->newSanNotificationLogRepo = app(NewSanNotificationLogRepository::class);

        // Iniciar el servicio con los mocks
        $this->newSanService = new NewSanService(
            $this->iflowApiServiceMock,
            $this->newSanApiServiceMock,
            $this->newSanOrderRepo,
            $this->newSanOrderInformedRepo,
            $this->newSanNotificationLogRepo
        );
    }

    /** @dataProvider parametersNotificationLogs */
    public function test_notification_logs_devuelve_paginado_esperado(int $logEntries, array $params)
    {
        if ($logEntries > 0) {
            NewSanNotificationLog::factory($logEntries)->create();
        }

        $request = Request::create(route('v1.newsan.notification-logs', $params), 'GET');

        $response = $this->newSanService->notificationLogs($request);

        $this->assertIsArray($response);
        $this->assertArrayHasKey('data', $response);

        $this->assertCount(min($params['per_page'], max(0, $logEntries - $params['per_page'] * ($params['current_page'] - 1))), $response['data']);
        $this->assertArrayHasKey('total', $response);
        $this->assertSame($logEntries, $response['total']);

        $this->assertArrayHasKey('perPage', $response);
        $this->assertSame($params['per_page'], $response['perPage']);

        $this->assertArrayHasKey('currentPage', $response);
        $this->assertSame($params['current_page'], $response['currentPage']);

        $lastPage = max((int) ceil($logEntries / $params['per_page']), 1);
        $this->assertArrayHasKey('lastPage', $response);
        $this->assertSame($lastPage, $response['lastPage']);
    }

    /** @dataProvider parametersExportNotificationLog */
    public function test_export_notification_log(array $paramsCreateLog, array $apiIds)
    {
        Excel::fake();

        $newSanLog = NewSanNotificationLog::factory($paramsCreateLog)->create();

        // $pe = null;
        // foreach ($apiIds as $value) {
        //     $pe = NewSanOrderInformed::factory()->create(['api_id' => $value]);
        // }

        $dateTimeNow = date('d-m-Y_H-i-s');
        $fileName    = 'NewSan_notificados_'.$newSanLog->id.'_'.$dateTimeNow.'.csv';

        $columns = [
            'API ID'      => 'api_id',
            'ORDER ID'    => 'order_id',
            'SHIPMENT ID' => 'shipment_id',
            'TRACKING ID' => 'tracking_id',
            'STATE ID'    => 'state_id',
            'MESSAGE'     => 'message',
            'STATE DATE'  => 'state_date',
            'FINALIZED'   => 'finalized',
            'UPDATED AT'  => 'updated_at',
        ];

        $this->newSanService->exportNotificationLog($newSanLog->id, $columns);

        Excel::assertDownloaded($fileName);
        /*
        Excel::assertDownloaded($fileName, function(NewSanOrderInformedExport $export) use ($columns) {
            $exportedRows = $export->collection();
            // Assert that the correct export is downloaded.
            // return $export->collection()->contains('1234;');
            // Aquí asertamos que cada fila tiene las claves de $columns
            foreach ($exportedRows as $row) {
                foreach (array_values($columns) as $column) {
                    $this->assertTrue(isset($row[$column]), "The $column does not exist in the exported row");
                }
            }

            // Si quieres también puedes verificar si los valores son los esperados
            // $this->assertEquals('expectedValue', $row['columnKey']);

            return true;
        });
        */
    }

    public static function parametersExportNotificationLog()
    {
        return [
            '2 notificados, 1 finalizado' => [
                [
                    'message'  => 'Se notificaron 2 orders a la api de NewSan. Los finalizados son: 1',
                    'notified' => json_encode([
                        1234,
                        4321,
                    ]),
                    'finalized' => json_encode([
                        1234,
                    ]),
                ],
                [
                    1234,
                    4321,
                ],
            ],
        ];
    }

    public static function parametersNotificationLogs()
    {
        return [
            '0 registros en log' => [
                0,
                [
                    'current_page' => 1,
                    'per_page'     => 10,
                    'column'       => 'created_at',
                    'order_by'     => 'desc',
                ],
            ],
            '2 registros en log' => [
                2,
                [
                    'current_page' => 2,
                    'per_page'     => 1,
                    'column'       => 'created_at',
                    'order_by'     => 'desc',
                ],
            ],
            '10 registros en log' => [
                10,
                [
                    'current_page' => 2,
                    'per_page'     => 5,
                    'column'       => 'created_at',
                    'order_by'     => 'desc',
                ],
            ],
        ];
    }

    /** @dataProvider parametersProcessNotFinalizedOrders */
    public function test_process_not_finalized_orders_obtiene_ordenes_no_finalizadas($quantity)
    {
        NewSanOrder::factory($quantity)->create(['finalized' => false]);

        $this->iflowApiServiceMock->expects($this->once())
            ->method('getStatusOrder')
            ->willReturn([
                'results' => [
                    'shippings' => [
                        [
                            'states' => [
                                [
                                    'state_name' => 'Registrado',
                                    'state_id'   => 1,
                                    'details'    => 'Tu pedido está siendo preparado por el vendedor',
                                    'state_date' => '05/09/2023 11:24',
                                    'reason'     => [],
                                ],
                            ],
                        ],
                    ],
                ],
            ]);

        $this->newSanService->processNotFinalizedOrders();
    }

    public static function parametersProcessNotFinalizedOrders()
    {
        return [
            '1 orden no finalizada' => [
                1,
            ],
        ];
    }

    /** @dataProvider parametersUpdateNewSanOrder */
    public function test_update_last_state_new_san_order_actualiza_estado_correctamente(array $createData, array $lastState, bool $finalized)
    {
        // Creo una nueva orden para posteriormente actualizarla
        NewSanOrder::factory()->create($createData);

        $orderUpdated = $this->newSanService->updateLastStateNewSanOrder($createData, $lastState);

        // Me aseguro que la orden actualizada sea la misma que cree, excepto por el state y finalized
        $this->assertDatabaseHas('NewSan_orders', [
            'api_id'      => $orderUpdated['api_id'],
            'order_id'    => $orderUpdated['order_id'],
            'shipment_id' => $orderUpdated['shipment_id'],
            'tracking_id' => $orderUpdated['tracking_id'],
            'state'       => $lastState['state_name'],
            'date'        => $orderUpdated['date'],
            'finalized'   => $finalized,
        ]);

        // Me aseguro que sea solo un registro por cada vez que actualizo
        $this->assertDatabaseCount('NewSan_orders', 1);
    }

    public static function parametersUpdateNewSanOrder()
    {
        return [
            'orden con estado Registrado, ultimo estado No Entregado, se guarda finalized en false' => [
                [
                    'api_id'      => 22537866,
                    'order_id'    => '97200000291299',
                    'shipment_id' => 'RRZ0000002105491',
                    'tracking_id' => 'OR0022306581',
                    'state'       => 'Registrado',
                    'date'        => '06/09/2023',
                ],
                [
                    'state_name' => 'No Entregado',
                    'state_id'   => 26,
                    'details'    => 'Visitamos tu domicilio el día de hoy pero no te encontramos',
                    'state_date' => '12/09/2023 09:46',
                    'reason'     => [
                        'id'          => 1,
                        'description' => 'CERRADO-AUSENTE',
                    ],
                ],
                false,
            ],
            'orden con estado Descargado, ultimo estado No Entregado, se guarda finalized en false' => [
                [
                    'api_id'      => 22537866,
                    'order_id'    => '97200023291299',
                    'shipment_id' => 'RRZ0022002105491',
                    'tracking_id' => 'OR0022306591',
                    'state'       => 'Descargado',
                    'date'        => '06/09/2023',
                ],
                [
                    'state_name' => 'No Entregado',
                    'state_id'   => 26,
                    'details'    => 'Visitamos tu domicilio el día de hoy pero no te encontramos',
                    'state_date' => '12/09/2023 09:46',
                    'reason'     => [
                        'id'          => 1,
                        'description' => 'CERRADO-AUSENTE',
                    ],
                ],
                false,
            ],
            'orden con estado Registrado, ultimo estado Entregado, se guarda finalized en true' => [
                [
                    'api_id'      => 126413,
                    'order_id'    => '97200000291299',
                    'shipment_id' => 'RRZ0000073105491',
                    'tracking_id' => 'OR0022306581',
                    'state'       => 'Registrado',
                    'date'        => '12/09/2023',
                ],
                [
                    'state_name' => 'Entregado',
                    'state_id'   => 25,
                    'details'    => '¡Tu pedido fue entregado! ¡Que lo disfrutes!',
                    'state_date' => '13/09/2023 15:56',
                    'reason'     => [],
                ],
                true,
            ],
            'orden con estado Pedido en Distribución, ultimo estado En proceso de devolucion, se guarda finalized en true' => [
                [
                    'api_id'      => 126418,
                    'order_id'    => '97200000291299',
                    'shipment_id' => 'RRZ0000802105491',
                    'tracking_id' => 'OR0022306581',
                    'state'       => 'Pedido en Distribución',
                    'date'        => '07/09/2023',
                ],
                [
                    'state_name' => 'En proceso de devolucion',
                    'state_id'   => 51,
                    'details'    => '¡Tu pedido fue entregado! ¡Que lo disfrutes!',
                    'state_date' => '13/09/2023 15:56',
                    'reason'     => [],
                ],
                true,
            ],
            "orden con estado '', ultimo estado En proceso de devolucion, se guarda finalized en true" => [
                [
                    'api_id'      => 126418,
                    'order_id'    => '97200000291299',
                    'shipment_id' => 'RRZ0000802105491',
                    'tracking_id' => 'OR0022306581',
                    'state'       => '',
                    'date'        => '07/09/2023',
                ],
                [
                    'state_name' => 'En proceso de devolucion',
                    'state_id'   => 51,
                    'details'    => '¡Tu pedido fue entregado! ¡Que lo disfrutes!',
                    'state_date' => '13/09/2023 15:56',
                    'reason'     => [],
                ],
                true,
            ],
        ];
    }

    /** @dataProvider parametersUpdateNewSanOrderInformed */
    public function test_update_new_san_orders_informed_actualiza_tabla_correctamente(array $orderUpdatedArray, array $lastStateArray)
    {
        $orderUpdated = NewSanOrder::factory()->create($orderUpdatedArray);

        $this->newSanService->updateOrCreateNewSanOrderInformed($orderUpdated, $lastStateArray);

        // Verifico la creacion en NewSan_orders_informed
        $this->assertDatabaseHas('NewSan_orders_informed', [
            'api_id'      => $orderUpdated['api_id'],
            'order_id'    => $orderUpdated['order_id'],
            'shipment_id' => $orderUpdated['shipment_id'],
            'tracking_id' => $orderUpdated['tracking_id'],
            'state_id'    => $lastStateArray['state_id'],
            'state_name'  => $lastStateArray['state_name'],
            'message'     => $lastStateArray['details'],
            'state_date'  => $lastStateArray['state_date'],
            'finalized'   => false,
        ]);

        // Me aseguro que se creo un solo registro
        $this->assertDatabaseCount('NewSan_orders_informed', 1);

        $this->newSanService->updateOrCreateNewSanOrderInformed($orderUpdated, $lastStateArray);

        // Verifico que actualize el mismo registro en NewSan_orders_informed
        $this->assertDatabaseHas('NewSan_orders_informed', [
            'api_id'      => $orderUpdated['api_id'],
            'order_id'    => $orderUpdated['order_id'],
            'shipment_id' => $orderUpdated['shipment_id'],
            'tracking_id' => $orderUpdated['tracking_id'],
            'state_id'    => $lastStateArray['state_id'],
            'state_name'  => $lastStateArray['state_name'],
            'message'     => $lastStateArray['details'],
            'state_date'  => $lastStateArray['state_date'],
            'finalized'   => false,
        ]);

        // Me aseguro que se creo un solo registro al final de todo el proceso
        $this->assertDatabaseCount('NewSan_orders_informed', 1);
    }

    public static function parametersUpdateNewSanOrderInformed()
    {
        return [
            'Orden finalizada, ultimo estado Entregado, se guarda en NewSan_orders_informed con finalized en false' => [
                [
                    'api_id'      => 22537866,
                    'order_id'    => '97200000291299',
                    'shipment_id' => 'RRZ0000002105491',
                    'tracking_id' => 'OR0022306581',
                    'state'       => 'Registrado',
                    'date'        => '06/09/2023',
                    'finalized'   => true,
                ],
                [
                    'state_name' => 'Entregado',
                    'state_id'   => 25,
                    'details'    => '¡Tu pedido fue entregado! ¡Que lo disfrutes!',
                    'state_date' => '13/09/2023 15:56',
                    'reason'     => [],
                ],
            ],
            'Orden Descargada, ultimo estado Entregado, se guarda en NewSan_orders_informed con finalized en false' => [
                [
                    'api_id'      => 22537867,
                    'order_id'    => '97200023291299',
                    'shipment_id' => 'RRZ0022002105491',
                    'tracking_id' => 'OR0022306591',
                    'state'       => 'Descargado',
                    'date'        => '06/09/2023',
                    'finalized'   => true,
                ],
                [
                    'state_name' => 'Entregado',
                    'state_id'   => 25,
                    'details'    => '¡Tu pedido fue entregado! ¡Que lo disfrutes!',
                    'state_date' => '13/09/2023 15:56',
                    'reason'     => [],
                ],
            ],
            'Orden Descargada, ultimo estado En proceso de devolucion, se guarda en NewSan_orders_informed con finalized en false' => [
                [
                    'api_id'      => 22537867,
                    'order_id'    => '97200023291299',
                    'shipment_id' => 'RRZ0022002105491',
                    'tracking_id' => 'OR0022306591',
                    'state'       => 'Descargado',
                    'date'        => '06/09/2023',
                    'finalized'   => true,
                ],
                [
                    'state_name' => 'En proceso de devolucion',
                    'state_id'   => 25,
                    'details'    => '¡Tu pedido fue entregado! ¡Que lo disfrutes!',
                    'state_date' => '13/09/2023 15:56',
                    'reason'     => [],
                ],
            ],
        ];
    }

    /** @dataProvider parametersProcessNotify */
    public function test_process_notify_informa_correctamente(array $ordersInformed, int $finalizados)
    {
        foreach ($ordersInformed as $item) {
            NewSanOrderInformed::factory()->create($item);
        }

        $this->newSanApiServiceMock->expects($this->any())
            ->method('postStatus')
            ->willReturnOnConsecutiveCalls(
                [
                    'code' => 200,
                ],
                [
                    'code' => 200,
                ],
            );

        $this->newSanService->processNotify();

        $count = NewSanOrderInformed::where('finalized', true)->count();

        $this->assertSame($finalizados, $count);
    }

    public static function parametersProcessNotify()
    {
        return [
            'dos ordenes no finalizadas, 0 marcadas como finalized' => [
                [
                    [
                        'api_id'      => 22537867,
                        'order_id'    => '97200023291299',
                        'shipment_id' => 'RRZ0022002105491',
                        'tracking_id' => 'OR0022306591',
                        'state_id'    => '26',
                        'state_name'  => 'No Entregado',
                        'message'     => 'Visitamos tu domicilio el día de hoy pero no te encontramos',
                        'state_date'  => '11/09/2023 10:20',
                        'finalized'   => false,
                    ],
                    [
                        'api_id'      => 22537868,
                        'order_id'    => '97200023291290',
                        'shipment_id' => 'RRZ0022002105490',
                        'tracking_id' => 'OR0022306581',
                        'state_id'    => '26',
                        'state_name'  => 'No Entregado',
                        'message'     => 'Visitamos tu domicilio el día de hoy pero no te encontramos',
                        'state_date'  => '11/09/2023 10:20',
                        'finalized'   => false,
                    ],
                ],
                0,
            ],
            'dos ordenes no finalized, 2 marcadas como finalized' => [
                [
                    [
                        'api_id'      => 22537867,
                        'order_id'    => '97200023291299',
                        'shipment_id' => 'RRZ0022002105491',
                        'tracking_id' => 'OR0022306591',
                        'state_id'    => '26',
                        'state_name'  => 'Entregado',
                        'message'     => 'Visitamos tu domicilio el día de hoy pero no te encontramos',
                        'state_date'  => '11/09/2023 10:20',
                        'finalized'   => false,
                    ],
                    [
                        'api_id'      => 22537868,
                        'order_id'    => '97200023291290',
                        'shipment_id' => 'RRZ0022002105490',
                        'tracking_id' => 'OR0022306581',
                        'state_id'    => '26',
                        'state_name'  => 'En proceso de devolucion',
                        'message'     => 'Visitamos tu domicilio el día de hoy pero no te encontramos',
                        'state_date'  => '11/09/2023 10:20',
                        'finalized'   => false,
                    ],
                ],
                2,
            ],
        ];
    }

    /** @dataProvider parametersNotifyOrdersRepetidos */
    public function test_notify_no_notifica_el_mismo_estado(array $orderInformedData, array $expectedResponse, array $responseApi)
    {
        $orderInformed = NewSanOrderInformed::factory()->create($orderInformedData);
        $collection    = new \Illuminate\Database\Eloquent\Collection([$orderInformed]);

        $newSanOrderInformedRepo = $this->createMock(NewSanOrderInformedRepository::class);

        $newSanOrderInformedRepo->expects($this->once())
            ->method('getUnfinalizedOrders')
            ->willReturn($collection);

        $this->newSanApiServiceMock->expects($this->any())
            ->method('postStatus')
            ->willReturn($responseApi);

        $newSanService = new NewSanService(
            $this->iflowApiServiceMock,
            $this->newSanApiServiceMock,
            $this->newSanOrderRepo,
            $newSanOrderInformedRepo,
            $this->newSanNotificationLogRepo
        );

        $response = $newSanService->notifyOrders(new Request());

        $this->assertSame($expectedResponse, $response);
    }

    public static function parametersNotifyOrdersRepetidos()
    {
        return [
            'orden informada con estado No Entregado, mismo estado notificado anteriormente' => [
                [
                    'api_id'              => 1122334455,
                    'order_id'            => '97200000288991',
                    'shipment_id'         => 'RRZ0000002102879',
                    'tracking_id'         => 'OR0022303160',
                    'state_id'            => 26,
                    'state_name'          => 'No Entregado',
                    'message'             => 'No pudimos visitar tu domicilio el día de hoy, volveremos a intentarlo a la brevedad',
                    'state_date'          => '08/09/2023 18:21',
                    'last_notified_state' => 'No Entregado',
                    'finalized'           => 0,
                ],
                [
                    'notifications' => 0,
                    'finalized'     => 0,
                ],
                [
                    'code' => 200,
                ],
            ],
            'orden informada con estado Registrado, mismo estado notificado anteriormente' => [
                [
                    'api_id'              => 1122334455,
                    'order_id'            => '97200000288991',
                    'shipment_id'         => 'RRZ0000002102879',
                    'tracking_id'         => 'OR0022303160',
                    'state_id'            => 1,
                    'state_name'          => 'Registrado',
                    'message'             => 'No pudimos visitar tu domicilio el día de hoy, volveremos a intentarlo a la brevedad',
                    'state_date'          => '08/09/2023 18:21',
                    'last_notified_state' => 'Registrado',
                    'finalized'           => 0,
                ],
                [
                    'notifications' => 0,
                    'finalized'     => 0,
                ],
                [
                    'code' => 200,
                ],
            ],
        ];
    }

    /** @dataProvider parametersNotifyOrders */
    public function test_notify_orders_lo_hace_correctamente(array $orders, array $listsOfStates, array $expectedResponse, array $responsesApi, array $notifiedArray, array $finalizedArray)
    {
        // Respuestas de API y repositorios simuladas
        $this->iflowApiServiceMock->method('getSellerOrdersGenerator')
            ->willReturn($this->mockOrderGenerator($orders));

        $this->iflowApiServiceMock->expects($this->any())
            ->method('getStatusOrder')
            ->willReturnOnConsecutiveCalls(
                ...$listsOfStates
            );

        $this->newSanApiServiceMock->expects($this->any())
            ->method('postStatus')
            ->willReturnOnConsecutiveCalls(
                ...$responsesApi
            );

        $response = $this->newSanService->notifyOrders(new Request());

        // se comprueba que el método hizo lo que esperábamos
        $this->assertSame($expectedResponse, $response);

        // se verifica que la cantidad en NewSan_orders_informed es la correcta
        $count = NewSanOrderInformed::count();
        $this->assertSame(count($orders), $count);

        $lastLog = NewSanNotificationLog::latest()->first();
        $this->assertSame(
            'Se notificaron '.$response['notifications'].' orders a la api de NewSan. Los finalizados son: '.$response['finalized'],
            $lastLog->message
        );
        $this->assertSame($notifiedArray, json_decode($lastLog->notified));
        $this->assertSame($finalizedArray, json_decode($lastLog->finalized));
    }

    public static function parametersNotifyOrders()
    {
        return [
            '2 ordenes nuevas; ultimos estados : No Entregado, Pedido en Distribución' => [
                [
                    [
                        'id'          => 22534195,
                        'order_id'    => '97200000288991',
                        'shipment_id' => 'RRZ0000002102879',
                        'tracking_id' => 'OR0022303160',
                        'state'       => 'No Entregado',
                        'date'        => '05/09/2023',
                    ],
                    [
                        'id'          => 22539345,
                        'order_id'    => '97200000293354',
                        'shipment_id' => 'RRZ0000002106807',
                        'tracking_id' => 'OR0022308057',
                        'state'       => 'No Entregado',
                        'date'        => '07/09/2023',
                    ],
                ],
                [
                    [
                        'results' => [
                            'shippings' => [
                                [
                                    'states' => [
                                        [
                                            'state_name' => 'Registrado',
                                            'state_id'   => 1,
                                            'details'    => 'Tu pedido está siendo preparado por el vendedor',
                                            'state_date' => '05/09/2023 11:24',
                                            'reason'     => [],
                                        ],
                                        [
                                            'state_name' => 'Retirado',
                                            'state_id'   => 8,
                                            'details'    => '¡Ya retiramos tu pedido, se encuentra camino a nuestro centro de distribución!',
                                            'state_date' => '06/09/2023 12:08',
                                            'reason'     => [],
                                        ],
                                        [
                                            'state_name' => 'Descargado',
                                            'state_id'   => 10,
                                            'details'    => 'Tu pedido llegó al centro de distribución de iFLOW',
                                            'state_date' => '06/09/2023 16:39',
                                            'reason'     => [],
                                        ],
                                        [
                                            'state_name' => 'Despachado a Nodo Interno',
                                            'state_id'   => 30,
                                            'details'    => 'Tu pedido se encuentra en camino a la sucursal más cercana a tu domicilio!',
                                            'state_date' => '07/09/2023 16:28',
                                            'reason'     => [],
                                        ],
                                        [
                                            'state_name' => 'Arribo a Nodo',
                                            'state_id'   => 31,
                                            'details'    => 'Tu pedido llegó a la sucursal más cercana a tu domicilio, en los próximos días te vamos a estar visitando',
                                            'state_date' => '08/09/2023 04:59',
                                            'reason'     => [],
                                        ],
                                        [
                                            'state_name' => 'Pedido en Distribución',
                                            'state_id'   => 35,
                                            'details'    => 'Tu pedido se encuentra en distribución y hoy te estaremos visitando!',
                                            'state_date' => '08/09/2023 06:07',
                                            'reason'     => [],
                                        ],
                                        [
                                            'state_name' => 'No Entregado',
                                            'state_id'   => 26,
                                            'details'    => 'Visitamos tu domicilio el día de hoy pero no te encontramos',
                                            'state_date' => '08/09/2023 09:30',
                                            'reason'     => [
                                                'id'          => 1,
                                                'description' => 'CERRADO-AUSENTE',
                                            ],
                                        ],
                                        [
                                            'state_name' => 'No Entregado',
                                            'state_id'   => 26,
                                            'details'    => 'Visitamos tu domicilio el día de hoy pero no te encontramos',
                                            'state_date' => '12/09/2023 09:46',
                                            'reason'     => [
                                                'id'          => 1,
                                                'description' => 'CERRADO-AUSENTE',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    [
                        'results' => [
                            'shippings' => [
                                [
                                    'states' => [
                                        [
                                            'state_name' => 'Registrado',
                                            'state_id'   => 1,
                                            'details'    => 'Tu pedido está siendo preparado por el vendedor',
                                            'state_date' => '05/09/2023 11:24',
                                            'reason'     => [],
                                        ],
                                        [
                                            'state_name' => 'Retirado',
                                            'state_id'   => 8,
                                            'details'    => '¡Ya retiramos tu pedido, se encuentra camino a nuestro centro de distribución!',
                                            'state_date' => '06/09/2023 12:08',
                                            'reason'     => [],
                                        ],
                                        [
                                            'state_name' => 'Descargado',
                                            'state_id'   => 10,
                                            'details'    => 'Tu pedido llegó al centro de distribución de iFLOW',
                                            'state_date' => '06/09/2023 16:39',
                                            'reason'     => [],
                                        ],
                                        [
                                            'state_name' => 'Pedido en Distribución',
                                            'state_id'   => 35,
                                            'details'    => 'Tu pedido se encuentra en distribución y hoy te estaremos visitando!',
                                            'state_date' => '08/09/2023 06:07',
                                            'reason'     => [],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'notifications' => 2,
                    'finalized'     => 0,
                ],
                [
                    [
                        'code' => 200,
                    ],
                    [
                        'code' => 200,
                    ],
                ],
                [
                    22534195,
                    22539345,
                ],
                [],
            ],
            '3 ordenes nuevas; ultimos estados : No Entregado, Pedido en Distribución, En proceso de devolucion' => [
                [
                    [
                        'id'          => 22534195,
                        'order_id'    => '97200000288991',
                        'shipment_id' => 'RRZ0000002102879',
                        'tracking_id' => 'OR0022303160',
                        'state'       => 'No Entregado',
                        'date'        => '05/09/2023',
                    ],
                    [
                        'id'          => 22539345,
                        'order_id'    => '97200000293354',
                        'shipment_id' => 'RRZ0000002106807',
                        'tracking_id' => 'OR0022308057',
                        'state'       => 'No Entregado',
                        'date'        => '07/09/2023',
                    ],
                    [
                        'id'          => 22495644,
                        'order_id'    => '9942000006200474496',
                        'shipment_id' => 'RRZ0000002076503',
                        'tracking_id' => 'OR0022267441',
                        'state'       => 'En proceso de devolucion',
                        'date'        => '07/08/2023',
                    ],
                ],
                [
                    [
                        'results' => [
                            'shippings' => [
                                [
                                    'states' => [
                                        [
                                            'state_name' => 'Registrado',
                                            'state_id'   => 1,
                                            'details'    => 'Tu pedido está siendo preparado por el vendedor',
                                            'state_date' => '05/09/2023 11:24',
                                            'reason'     => [],
                                        ],
                                        [
                                            'state_name' => 'Retirado',
                                            'state_id'   => 8,
                                            'details'    => '¡Ya retiramos tu pedido, se encuentra camino a nuestro centro de distribución!',
                                            'state_date' => '06/09/2023 12:08',
                                            'reason'     => [],
                                        ],
                                        [
                                            'state_name' => 'Descargado',
                                            'state_id'   => 10,
                                            'details'    => 'Tu pedido llegó al centro de distribución de iFLOW',
                                            'state_date' => '06/09/2023 16:39',
                                            'reason'     => [],
                                        ],
                                        [
                                            'state_name' => 'Despachado a Nodo Interno',
                                            'state_id'   => 30,
                                            'details'    => 'Tu pedido se encuentra en camino a la sucursal más cercana a tu domicilio!',
                                            'state_date' => '07/09/2023 16:28',
                                            'reason'     => [],
                                        ],
                                        [
                                            'state_name' => 'Arribo a Nodo',
                                            'state_id'   => 31,
                                            'details'    => 'Tu pedido llegó a la sucursal más cercana a tu domicilio, en los próximos días te vamos a estar visitando',
                                            'state_date' => '08/09/2023 04:59',
                                            'reason'     => [],
                                        ],
                                        [
                                            'state_name' => 'Pedido en Distribución',
                                            'state_id'   => 35,
                                            'details'    => 'Tu pedido se encuentra en distribución y hoy te estaremos visitando!',
                                            'state_date' => '08/09/2023 06:07',
                                            'reason'     => [],
                                        ],
                                        [
                                            'state_name' => 'No Entregado',
                                            'state_id'   => 26,
                                            'details'    => 'Visitamos tu domicilio el día de hoy pero no te encontramos',
                                            'state_date' => '08/09/2023 09:30',
                                            'reason'     => [
                                                'id'          => 1,
                                                'description' => 'CERRADO-AUSENTE',
                                            ],
                                        ],
                                        [
                                            'state_name' => 'No Entregado',
                                            'state_id'   => 26,
                                            'details'    => 'Visitamos tu domicilio el día de hoy pero no te encontramos',
                                            'state_date' => '12/09/2023 09:46',
                                            'reason'     => [
                                                'id'          => 1,
                                                'description' => 'CERRADO-AUSENTE',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    [
                        'results' => [
                            'shippings' => [
                                [
                                    'states' => [
                                        [
                                            'state_name' => 'Registrado',
                                            'state_id'   => 1,
                                            'details'    => 'Tu pedido está siendo preparado por el vendedor',
                                            'state_date' => '05/09/2023 11:24',
                                            'reason'     => [],
                                        ],
                                        [
                                            'state_name' => 'Retirado',
                                            'state_id'   => 8,
                                            'details'    => '¡Ya retiramos tu pedido, se encuentra camino a nuestro centro de distribución!',
                                            'state_date' => '06/09/2023 12:08',
                                            'reason'     => [],
                                        ],
                                        [
                                            'state_name' => 'Descargado',
                                            'state_id'   => 10,
                                            'details'    => 'Tu pedido llegó al centro de distribución de iFLOW',
                                            'state_date' => '06/09/2023 16:39',
                                            'reason'     => [],
                                        ],
                                        [
                                            'state_name' => 'Pedido en Distribución',
                                            'state_id'   => 35,
                                            'details'    => 'Tu pedido se encuentra en distribución y hoy te estaremos visitando!',
                                            'state_date' => '08/09/2023 06:07',
                                            'reason'     => [],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    [
                        'results' => [
                            'shippings' => [
                                [
                                    'states' => [
                                        [
                                            'state_name' => 'Registrado',
                                            'state_id'   => 1,
                                            'details'    => 'Tu pedido está siendo preparado por el vendedor',
                                            'state_date' => '05/09/2023 11:24',
                                            'reason'     => [],
                                        ],
                                        [
                                            'state_name' => 'Retirado',
                                            'state_id'   => 8,
                                            'details'    => '¡Ya retiramos tu pedido, se encuentra camino a nuestro centro de distribución!',
                                            'state_date' => '06/09/2023 12:08',
                                            'reason'     => [],
                                        ],
                                        [
                                            'state_name' => 'Descargado',
                                            'state_id'   => 10,
                                            'details'    => 'Tu pedido llegó al centro de distribución de iFLOW',
                                            'state_date' => '06/09/2023 16:39',
                                            'reason'     => [],
                                        ],
                                        [
                                            'state_name' => 'Despachado a Nodo Interno',
                                            'state_id'   => 30,
                                            'details'    => 'Tu pedido se encuentra en camino a la sucursal más cercana a tu domicilio!',
                                            'state_date' => '07/09/2023 16:28',
                                            'reason'     => [],
                                        ],
                                        [
                                            'state_name' => 'Arribo a Nodo',
                                            'state_id'   => 31,
                                            'details'    => 'Tu pedido llegó a la sucursal más cercana a tu domicilio, en los próximos días te vamos a estar visitando',
                                            'state_date' => '08/09/2023 04:59',
                                            'reason'     => [],
                                        ],
                                        [
                                            'state_name' => 'Devolucion a Central',
                                            'state_id'   => 53,
                                            'details'    => 'Tu pedido será devuelto a nuestro centro de distribución',
                                            'state_date' => '07/09/2023 13:42',
                                            'reason'     => [],
                                        ],
                                        [
                                            'state_name' => 'En proceso de devolucion',
                                            'state_id'   => 51,
                                            'details'    => 'Tu pedido se encuentra en proceso de devolución al vendedor',
                                            'state_date' => '11/09/2023 23:50',
                                            'reason'     => [],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'notifications' => 3,
                    'finalized'     => 1,
                ],
                [
                    [
                        'code' => 200,
                    ],
                    [
                        'code' => 200,
                    ],
                    [
                        'code' => 200,
                    ],
                ],
                [
                    22495644,
                    22534195,
                    22539345,
                ],
                [
                    22495644,
                ],
            ],
        ];
    }

    protected function mockOrderGenerator($orders)
    {
        foreach ($orders as $order) {
            yield $order;
        }
    }
}
