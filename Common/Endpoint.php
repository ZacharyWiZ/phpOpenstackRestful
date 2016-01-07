<?php
/**
 * Created by PhpStorm.
 * User: wiz
 * Date: 2015/12/17
 * Time: 10:10
 */
require_once("Openstack.php");

class Endpoint {
    //@var string
    private $publicUrl;

    //@var string
    private $internalUrl;

    //@var string
    private $adminUrl;

    //@var string
    private $region;

    /**
     * @param $object is json object
     * @param $client Openstack
     * @return Endpoint
     */
    public static function factory($object, Openstack $client) {
        $endpoint = new self();
        if (isset($object->publicURL)) {
            $endpoint->setPublicUrl($endpoint->getFormatUrl($object->publicURL, $client));
        }
        if (isset($object->internalURL)) {
            $endpoint->setInternalUrl($endpoint->getFormatUrl($object->internalURL, $client));
        }
        if (isset($object->adminURL)) {
            $endpoint->setAdminUrl($endpoint->getFormatUrl($object->adminURL, $client));
        }
        if (isset($object->region)) {
            $endpoint->setRegion($object->region);
        }
        return $endpoint;
    }

    /**
     * @param string
     * @return $this
     */
    public function setPublicUrl($publicUrl) {
        $this->publicUrl = $publicUrl;
        return $this;
    }

    //@return string
    public function getPublicUrl() {
        return $this->publicUrl;
    }

    /**
     * @param string
     * @return $this
     */
    public function setInternalUrl($internalUrl) {
        $this->internalUrl = $internalUrl;
        return $this;
    }

    //@return string
    public function getInternalUrl() {
        return $this->internalUrl;
    }

    /**
     * @param string
     * @return $this
     */
    public function setAdminUrl($adminUrl) {
        $this->adminUrl = $adminUrl;
        return $this;
    }

    //@return string
    public function getAdminUrl() {
        return $this->adminUrl;
    }

    /**
     * @param string
     * @return $this
     */
    public function setRegion($region) {
        $this->region = $region;
        return $this;
    }

    //@return string
    public function getRegion() {
        return $this->region;
    }

    /**
     * @param $url like: http://controller5:5000/v2.0/*********
     * @return as : http://172.16.4.31:5000/v2.0/************
     */
    private function getFormatUrl($url, Openstack $client) {
        $port_and_tail = strrchr($url, ":"); //like :5000/v2.0/*******
        $url = $client->getAuthUrl() . $port_and_tail;
        echo "<br/>Endpoint:: getFormatuUrl : $url<br/>";
        return $url;
    }

}