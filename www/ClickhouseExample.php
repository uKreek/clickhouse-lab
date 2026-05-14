<?php

namespace App;

use App\Helpers\ClientFactory;

class ClickhouseExample
{
    private $client;

    public function __construct()
    {
        // ИСправлено: обращаемся к хосту 'clickhouse', а не 'localhost'
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