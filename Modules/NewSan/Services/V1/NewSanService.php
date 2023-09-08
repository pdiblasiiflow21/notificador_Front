<?php

declare(strict_types=1);

namespace Modules\NewSan\Services\V1;

use App\Service\V1\IflowApiService;
use Illuminate\Http\Request;

class NewSanService
{
    private $iflowApiService;

    public function __construct(
        IflowApiService $iflowApiService
    ) {
        $this->iflowApiService = $iflowApiService;
    }

    public function getToken(Request $request)
    {
        return $this->iflowApiService->getToken($request);
    }

    public function getStatusOrder(string $trackId)
    {
        return $this->iflowApiService->getStatusOrder($trackId);
    }

    public function getSellerOrders(Request $request)
    {
        return $this->iflowApiService->getSellerOrders($request);
    }
}
