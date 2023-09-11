<?php

declare(strict_types=1);

namespace App\Service\V1;

use App\Config\NewSanConfig;
use App\Repository\V1\ApiLogRepository;
use GuzzleHttp\Client;

class NewSanApiService extends BaseApiService
{
    private $newSanConfig;

    public function __construct(
        Client $client,
        ApiLogRepository $apiLogRepository,
        NewSanConfig $newSanConfig
    ) {
        parent::__construct($client, $apiLogRepository);
        $this->newSanConfig = $newSanConfig;
    }

    public function postStatus(array $orderData)
    {
        return $this->executeApiCall(function () use ($orderData) {
            $url = $this->newSanConfig->getUrlnotificationsMethod().'/'.$this->newSanConfig->getUserPost();

            $params = [
                'headers' => [
                    'auth-key'     => $this->newSanConfig->getKeyPost(),
                    'Content-Type' => 'application/json',
                ],
                'json' => $orderData,
            ];

            return $this->callApi($url, $params, 'post');
        }, 'api_newsan', 'postStatus');
    }
}
