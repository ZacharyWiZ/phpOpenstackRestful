<?php
/**
 * Created by PhpStorm.
 * User: wiz
 * Date: 2015/12/16
 * Time: 22:13
 */

require_once(dirname(__FILE__)."/../Http/Client.php");
require_once("Endpoint.php");
require_once("ServiceInterface.php");

abstract class AbstractService implements ServiceInterface {
    //@var Http\Client
    protected $client;

    //@var Service\Endpoint
    protected $endpoint;

    //@param /Http/Client
    public function setClient(Client $client) {
        $this->client = $client;
    }

    public function getClient() {
        return $this->client;
    }

    //@param Endpoint
    public function setEndpoint($endpoint) {
        $this->endpoint = $endpoint;
    }

    public function getEndpoint() {
        return $this->endpoint;
    }
}