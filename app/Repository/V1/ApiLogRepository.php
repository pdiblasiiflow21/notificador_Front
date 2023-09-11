<?php

declare(strict_types=1);

namespace App\Repository\V1;

use App\Models\ApiLog;
use App\Repository\EloquentRepository;

class ApiLogRepository extends EloquentRepository
{
    public function __construct()
    {
        parent::__construct(new ApiLog());
    }

    /**
     * Guarda la información de la respuesta de la API en la tabla api_logs.
     *
     * Este método recopila y guarda información esencial sobre una llamada a la API, incluido el endpoint,
     * las credenciales de la solicitud, el código de estado de la respuesta, la respuesta misma y la duración de la llamada.
     *
     * @param string $url                 El endpoint de la API al que se realizó la solicitud.
     * @param string $method              El método con el que se realizó la solicitud.
     * @param string $requestCredentials  Las credenciales y/o parámetros enviados en la solicitud.
     * @param int    $responseStatusCode  El código de estado HTTP de la respuesta.
     * @param string $response            El cuerpo de la respuesta recibida.
     * @param float  $duration            El tiempo que tardó en completarse la llamada a la API (en milisegundos).
     *
     * @return void
     */
    public function saveResponseApi(
        string $url,
        string $method,
        string $requestCredentials,
        int $responseStatusCode,
        string $response,
        float $duration
    ) {
        $dataLog = [
            'request_endpoint'     => $url,
            'request_method'       => $method,
            'request_credentials'  => $requestCredentials,
            'response_status_code' => $responseStatusCode,
            'response'             => $response,
            'response_time'        => $duration,
        ];

        $this->create($dataLog);
    }
}
