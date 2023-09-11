<?php

declare(strict_types=1);

namespace App\Service\V1;

use App\Config\IflowConfig;
use App\Repository\V1\ApiLogRepository;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Request;

class IflowApiService extends BaseApiService
{
    private $iflowConfig;

    public function __construct(
        Client $client,
        ApiLogRepository $apiLogRepository,
        IflowConfig $iflowConfig
    ) {
        parent::__construct($client, $apiLogRepository);
        $this->iflowConfig = $iflowConfig;
    }

    /**
     * Obtiene un token de autenticación desde la API de Iflow.
     *
     * Este método realiza una solicitud POST a la API de Iflow para obtener un token de autenticación.
     * El token se almacena internamente para ser reutilizado en futuras llamadas.
     *
     * @param  Request $request La petición HTTP actual, que debería contener los parámetros 'username' y 'password'.
     * @throws \Exception       Si ocurre un error durante la comunicación con la API de Iflow o si la respuesta de la API es inesperada.
     * @return string           El token de autenticación obtenido de la API de Iflow.
     */
    public function getToken(Request $request)
    {
        return $this->executeApiCall(function () use ($request) {
            $url    = $this->iflowConfig->getUrlLogin();
            $params = [
                'json' => [
                    '_username' => $request->input('username'),
                    '_password' => $request->input('password'),
                    'headers'   => [
                        'Content-Type' => 'application/json',
                        'Cookie'       => 'SERVERID=api_iflow21',
                    ],
                ],
            ];

            $response = $this->callApi($url, $params, 'post');

            $token = $response['token'];

            return $token;
        }, 'api_iflow', 'getToken');
    }

    /**
     * Obtiene el estado de un pedido específico desde la API de Iflow.
     *
     * Este método realiza una solicitud GET a la API de Iflow para recuperar el estado de un pedido
     * a partir de su identificador de seguimiento (`trackId`).
     * Utiliza un token previamente adquirido para autorizar la solicitud.
     *
     * @param  string $trackId El identificador de seguimiento del pedido que se quiere consultar.
     * @throws ClientException Si ocurre un error del cliente HTTP (por ejemplo, 401 Unauthorized).
     * @throws \Exception      Si ocurre un error general o inesperado durante la comunicación con la API de Iflow.
     * @return array           La respuesta de la API de Iflow que contiene el estado del pedido.
     */
    public function getStatusOrder(string $trackId)
    {
        return $this->executeApiCall(function () use ($trackId) {
            $token = request()->bearerToken();

            $url = $this->iflowConfig->getUrlStatusOrder().'/'.$trackId;

            $params = [
                'headers' => [
                    'Authorization' => 'Bearer '.$token,
                ],
            ];

            return $this->callApi($url, $params);
        }, 'api_iflow', 'getStatusOrder');
    }

    /**
     * Obtiene las órdenes del vendedor desde la API de Iflow.
     *
     * Este método realiza una solicitud GET a la API de Iflow para recuperar las órdenes del vendedor.
     * Utiliza un token previamente adquirido para autorizar la solicitud.
     *
     * @param  Request $request La petición HTTP actual, que puede contener parámetros de consulta 'page' y 'limit'.
     *
     * @throws ClientException  Si ocurre un error del cliente HTTP (por ejemplo, 401 Unauthorized).
     * @throws \Exception       Si ocurre un error general o inesperado durante la comunicación con la API de Iflow.
     * @return array            La respuesta de la API de Iflow que contiene las órdenes del vendedor.
     */
    public function getSellerOrders(Request $request)
    {
        return $this->executeApiCall(function () use ($request) {
            $token = $request->bearerToken();

            // Obtengo los parámetros de la consulta (query parameters) de la petición
            $page  = $request->input('page', 1);
            $limit = $request->input('limit', 100);

            // Agregar parámetros de la consulta a la URL
            $url = $this->iflowConfig->getUrlSellerOrders();
            $url = sprintf('%s?page=%s&limit=%s', $url, $page, $limit);

            $params = [
                'headers' => [
                    'Authorization' => 'Bearer '.$token,
                    'Content-Type'  => 'application/json',
                    'Cookie'        => 'Cookie_1=value; SERVERID=api_iflow21_n2',
                ],
            ];

            return $this->callApi($url, $params);
        }, 'api_iflow', 'getSellerOrders');
    }

    public function getSellerOrdersGenerator(Request $request): \Generator
    {
        $maxPage = $request->input('pages', 1);    // Límite máximo de páginas
        $limit   = $request->input('limit', 500);  // Órdenes por página

        for ($page = 1; $page <= $maxPage; $page++) {
            $request->merge(['page' => $page, 'limit' => $limit]);

            $orders = $this->getSellerOrders($request);

            // Si no hay más resultados, rompemos el bucle
            if (empty($orders['results'])) {
                break;
            }

            foreach ($orders['results'] as $order) {
                yield $order;  // Este es el punto donde "cedemos" cada orden uno por uno
            }
        }
    }
}
