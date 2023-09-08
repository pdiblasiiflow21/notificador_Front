<?php

declare(strict_types=1);

namespace App\Service\V1;

use App\Config\IflowConfig;
use App\Repository\V1\ApiLogRepository;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class IflowApiService
{
    private $client;

    private $config;

    private $apiLogRepository;

    private $token;

    public function __construct(
        Client $client,
        IflowConfig $config,
        ApiLogRepository $apiLogRepository
    ) {
        $this->client           = $client;
        $this->config           = $config;
        $this->apiLogRepository = $apiLogRepository;
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
        try {
            if ($this->token) {
                return $this->token;
            }

            $url    = $this->config->getUrlLogin();
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
        } catch (\Throwable $th) {
            Log::channel('api_iflow')->error('Respuesta de la api: '.$th->getMessage());

            throw new \Exception('Error al consumir la api de logueo de iflow');
        }
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
        try {
            $token = request()->bearerToken();

            $url = $this->config->getUrlStatusOrder().'/'.$trackId;

            $params = [
                'headers' => [
                    'Authorization' => 'Bearer '.$token,
                ],
            ];

            return $this->callApi($url, $params);
        } catch (ClientException $e) {
            $message    = '';
            $response   = $e->getResponse();
            $statusCode = $response ? $response->getStatusCode() : $e->getCode();

            if ($statusCode === Response::HTTP_UNAUTHORIZED) {
                $message = 'Token vencido. Unauthorized';
            }

            Log::channel('api_iflow')->error($message);

            throw new \Exception($message);
        } catch (\Throwable $th) {
            Log::channel('api_iflow')->error('Respuesta de la api: '.$th->getMessage());

            throw new \Exception('Error al obtener el estado del pedido en iflow');
        }
    }

    /**
     * Obtiene las órdenes del vendedor desde la API de Iflow.
     *
     * Este método realiza una solicitud GET a la API de Iflow para recuperar las órdenes del vendedor.
     * Utiliza un token previamente adquirido para autorizar la solicitud.
     *
     * @param  Request $request La petición HTTP actual, que puede contener parámetros de consulta 'page' y 'limit'.
     * @throws ClientException  Si ocurre un error del cliente HTTP (por ejemplo, 401 Unauthorized).
     * @throws \Exception       Si ocurre un error general o inesperado durante la comunicación con la API de Iflow.
     * @return array            La respuesta de la API de Iflow que contiene las órdenes del vendedor.
     */
    public function getSellerOrders(Request $request)
    {
        try {
            $token = $request->bearerToken();

            // Obtener parámetros de la consulta (query parameters) de la petición
            $page  = $request->input('page', 1);
            $limit = $request->input('limit', 100);

            // Agregar parámetros de la consulta a la URL
            $url = $this->config->getUrlSellerOrders();
            $url = sprintf('%s?page=%s&limit=%s', $url, $page, $limit);

            $params = [
                'headers' => [
                    'Authorization' => 'Bearer '.$token,
                    'Content-Type'  => 'application/json',
                    'Cookie'        => 'Cookie_1=value; SERVERID=api_iflow21_n2',
                ],
            ];

            return $this->callApi($url, $params);
        } catch (ClientException $e) {
            $message    = '';
            $response   = $e->getResponse();
            $statusCode = $response ? $response->getStatusCode() : $e->getCode();

            if ($statusCode === Response::HTTP_UNAUTHORIZED) {
                $message = 'Token vencido. Unauthorized';
            }

            Log::channel('api_iflow')->error($message);

            throw new \Exception($message);
        } catch (\Throwable $th) {
            Log::channel('api_iflow')->error('Respuesta de la api para getSellerOrders: '.$th->getMessage());

            throw new \Exception('Error al obtener las ordenes del vendedor en iflow');
        }
    }

    /**
     * Realiza una llamada a una API y registra la respuesta y la duración.
     *
     * Este método utiliza un cliente HTTP para hacer una solicitud a una URL dada con parámetros opcionales.
     * La función mide el tiempo que tarda la solicitud y guarda la respuesta y la duración en el log.
     *
     * @param  string $url      La URL a la que se hará la llamada de la API.
     * @param  array  $params   Los parámetros que se enviarán con la solicitud (como cabeceras, parámetros de consulta, cuerpo, etc.)
     * @param  string $method   El método HTTP para usar ('get', 'post', etc.). Por defecto es 'get'.
     *
     * @throws \GuzzleHttp\Exception\GuzzleException Si hay un problema con la solicitud HTTP.
     * @return array            La respuesta de la API, decodificada como una matriz asociativa.
     */
    private function callApi(string $url, array $params, string $method = 'get')
    {
        $startTime = microtime(true);

        $response = $this->client->$method($url, $params);

        $endTime  = microtime(true);
        $duration = ($endTime - $startTime) * 1000;

        $stream   = $response->getBody();
        $contents = (string) $stream;
        $json     = json_decode($contents, true);

        // $truncatedResponse = Str::limit($contents, 65000);
        $this->saveResponseApi($url, json_encode($params), $response->getStatusCode(), $contents, $duration);

        Log::channel('api_iflow')->info('Respuesta de la api para getSellerOrders: '.json_encode($json));

        return $json;
    }

    /**
     * Guarda la información de la respuesta de la API en un repositorio de logs.
     *
     * Este método recopila y guarda información esencial sobre una llamada a la API, incluido el punto final,
     * las credenciales de la solicitud, el código de estado de la respuesta, la respuesta misma y la duración de la llamada.
     *
     * @param string $url                 El punto final de la API al que se realizó la solicitud.
     * @param string $requestCredentials  Las credenciales y/o parámetros enviados en la solicitud.
     * @param int    $responseStatusCode  El código de estado HTTP de la respuesta.
     * @param string $response            El cuerpo de la respuesta recibida.
     * @param float  $duration            El tiempo que tardó en completarse la llamada a la API (en milisegundos).
     *
     * @return void
     */
    private function saveResponseApi(
        string $url,
        string $requestCredentials,
        int $responseStatusCode,
        string $response,
        float $duration
    ) {
        $dataLog = [
            'request_endpoint'     => $url,
            'request_credentials'  => $requestCredentials,
            'response_status_code' => $responseStatusCode,
            'response'             => $response,
            'response_time'        => $duration,
        ];

        $this->apiLogRepository->create($dataLog);
    }
}
