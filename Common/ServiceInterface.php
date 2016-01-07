<?php
/**
 * Created by PhpStorm.
 * User: wiz
 * Date: 2015/12/16
 * Time: 22:18
 */
require_once(dirname(__FILE__)."/../Http/Client.php");

interface ServiceInterface {
    public function setClient(Client $client);

    public function getClient();

    public function setEndpoint($endpoint);

    public function getEndpoint();

    public function getUrl();
}