<?php

namespace App;

use App\Helpers\ClientFactory;

class RedisExample
{
    public function __construct()
{
   $this->client = new Predis\Client('tcp://redis:6379');
}


public function setValue($key, $value)
{
   $this->client->set($key, $value);
}


public function getValue($key)
{
   return $this->client->get($key);
}

}
