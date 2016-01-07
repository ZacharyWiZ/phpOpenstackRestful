<?php
/**
 * Created by PhpStorm.
 * User: wiz
 * Date: 2015/12/17
 * Time: 21:50
 */

require_once(dirname(__FILE__) . "/../Common/CatalogService.php");
require_once(dirname(__FILE__) . "/../Http/Client.php");
require_once("ComputeServer.php");
require_once("Flavor.php");
require_once("Image.php");
require_once("FloatingIp.php");

/**
 * The computer class represents the Openstack Nova service
 *
 * It is constructed from a Openstack object and service name(e.g. compute)
 */

class ComputeService extends CatalogService{
    const DEFAULT_TYPE = 'compute';
    const DEFAULT_NAME = 'nova';

    public function hello() {
        echo "Hello world.<br/>";
        print_r($this->getEndpoint());
    }

    public function server($id = null) {
        return new ComputeServer($this, $id);
    }

    public function flavor($id = null) {
        return new Flavor($this, $id);
    }

    public function image($id = null) {
        return new Image($this, $id);
    }

    public function floatingIp() {
        return new floatingIp($this, null);
    }
 };