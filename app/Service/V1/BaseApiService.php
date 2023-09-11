<?php

declare(strict_types=1);

namespace App\Service\V1;

use App\Exceptions\Api\CredencialesInvalidasException;
use App\Exceptions\Api\TokenVencidoException;
use App\Repository\V1\ApiLogRepository;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class BaseApiService
{
    private $client;

    private $apiLogRepository;

    public function __construct(
        Client $client,
        ApiLogRepository $apiLogRepository
    ) {
        $this->client           = $client;
        $this->apiLogRepository = $apiLogRepository;
    }

    /**
     * Ejecuta una llamada API y maneja las excepciones asociadas.
     *
     * @param callable $callback La función que realiza la llamada API.
     * @param string $channelLogName Nombre del canal de log a utilizar.
     * @throws TokenVencidoException Si el token de la API ha vencido.
     * @throws CredencialesInvalidasException Si las credenciales son inválidas.
     * @throws \Exception Si ocurre otro error.
     * @return mixed El resultado devuelto por la llamada API.
     */
    protected function executeApiCall(callable $callback, string $channelLogName)
    {
        try {
            return $callback();
        } catch (ClientException $e) {
            $message    = '';
            $response   = $e->getResponse();
            $statusCode = $response ? $response->getStatusCode() : $e->getCode();

            if ($statusCode === Response::HTTP_UNAUTHORIZED) {
                $message = 'Token vencido. Unauthorized';
                Log::channel($channelLogName)->error($message);

                throw new TokenVencidoException($message);
            }

            // TODO: lanzar exception personalizada en otro caso particular

            throw new \Exception($message);
        } catch (\ErrorException $e) {
            if ($e->getCode() === 0) {
                $message = 'Credenciales Inválidas. Unauthorized';
                Log::channel($channelLogName)->error($message);

                throw new CredencialesInvalidasException($message);
            }
        } catch (\Throwable $th) {
            Log::channel($channelLogName)->error('Respuesta de la api: '.$th->getMessage());

            throw new \Exception('Error en la API');
        }
    }

    /**
     * Realiza una llamada API usando Guzzle y registra la respuesta.
     *
     * @param string $url La URL de la API.
     * @param array $params Los parámetros para la llamada API.
     * @param string $method El método HTTP para la llamada API (por defecto 'get').
     * @return array La respuesta de la API como un array asociativo.
     */
    protected function callApi(string $url, array $params, string $method = 'get'): array
    {
        $startTime = microtime(true);

        $response = $this->client->$method($url, $params);

        $endTime  = microtime(true);
        $duration = ($endTime - $startTime) * 1000;

        $stream   = $response->getBody();
        $contents = (string) $stream;
        $json     = json_decode($contents, true);

        $this->apiLogRepository->saveResponseApi($url, $method, json_encode($params), $response->getStatusCode(), $contents, $duration);

        return $json;
    }
}
