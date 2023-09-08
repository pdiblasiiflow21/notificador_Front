<?php

declare(strict_types=1);

namespace App\Config;

class IflowConfig
{
    public String $host;

    public String $loginMethod;

    public String $orderStateMethod;

    public String $sellerOrdersMethod;

    public function __construct()
    {
        $this->host               = config('app.iflow.host');
        $this->loginMethod        = config('app.iflow.login_method');
        $this->orderStateMethod   = config('app.iflow.order_state_method');
        $this->sellerOrdersMethod = config('app.iflow.get_seller_orders_method');
    }

    private function getBaseUrl()
    {
        return $this->host;
    }

    public function getUrlLogin()
    {
        return $this->getBaseUrl().'/'.$this->loginMethod;
    }

    public function getUrlStatusOrder()
    {
        return $this->getBaseUrl().'/'.$this->orderStateMethod;
    }

    public function getUrlSellerOrders()
    {
        return $this->getBaseUrl().'/'.$this->sellerOrdersMethod;
    }
}
