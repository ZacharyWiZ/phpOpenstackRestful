<?php
/**
 * Created by PhpStorm.
 * User: wiz
 * Date: 2015/12/18
 * Time: 10:57
 */

require_once(dirname(__FILE__) . "/../Common/AbstractService.php");

class IdentityService extends AbstractService{
    const DEFAULT_IDENTITY_URL_TAIL = ":5000/v2.0/tokens";

    static public function factory(Openstack $client) {
        $identity = new self();

        $identity->setClient($client);
        $identity->setEndpoint($client->getAuthUrl());

        return $identity;
    }

    public function getUrl($path = null) {
        $url = $this->getEndpoint();
        $path = $path ? : static::DEFAULT_IDENTITY_URL_TAIL;
        return $url . $path;
    }

    public function generateToken($json) {
        $client = $this->getClient();
        $url = $this->getUrl();
        $client->setUrl($url);
        return $this->getClient()->post_json($json);
    }

};