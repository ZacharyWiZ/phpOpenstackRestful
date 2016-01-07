<?php
/**
 * Created by PhpStorm.
 * User: wiz
 * Date: 2015/12/19
 * Time: 21:49
 */

require_once(dirname(__FILE__) . "/../Common/CatalogService.php");
require_once("VolumeServer.php");

class VolumeService extends CatalogService {
    const DEFAULT_TYPE = 'volume';
    const DEFAULT_NAME = 'cinder';

    public function hello() {
        echo "Hello world.<br/>";
        print_r($this->getEndpoint());
    }

    public function server($id = null) {
        return new VolumeServer($this, $id);
    }
};