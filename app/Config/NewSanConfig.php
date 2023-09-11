<?php

declare(strict_types=1);

namespace App\Config;

class NewSanConfig
{
    public String $host;

    public String $user;

    public String $key;

    public String $notificationsMethod;

    public function __construct()
    {
        $this->host                = config('app.newsan.host');
        $this->user                = config('app.newsan.user');
        $this->key                 = config('app.newsan.key');
        $this->notificationsMethod = config('app.newsan.notification_method');
    }

    private function getBaseUrl()
    {
        return $this->host;
    }

    public function getUrlnotificationsMethod()
    {
        return $this->getBaseUrl().'/'.$this->notificationsMethod;
    }

    public function getUserPost()
    {
        return $this->user;
    }

    public function getKeyPost()
    {
        return $this->key;
    }
}
