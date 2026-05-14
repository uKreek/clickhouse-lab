<?php

namespace App;

use App\Helpers\ClientFactory;

class ClickhouseExample
{
    private $client;

    public function __construct()
    {
        // инициализация клиента с базовым uri clickhouse
        $this->client = ClientFactory::make('http://clickhouse:8123/');
    }

    public function query($sql)
    {
        $response = $this->client->post('', [
            'body' => $sql
        ]);
        return $response->getBody()->getContents();
    }
}