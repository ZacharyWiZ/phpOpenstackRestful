<?php
/**
 * Created by PhpStorm.
 * User: wiz
 * Date: 2015/12/18
 * Time: 17:50
 */
require_once(dirname(__FILE__) . "/../Common/AbstractService.php");
require_once(dirname(__FILE__) . "/../Common/CatalogService.php");
require_once(dirname(__FILE__) . "/../Http/Client.php");
//require_once(dirname(__FILE__) . "/../ComputerService.php");

abstract class BaseResource {

    //@var : AbstractService
    protected $service;

    //@var : url_tail (e.g. /servers/   or /volumes/)
    protected $urlTail;

    //@var : id (e.g. compute_id or volumes_id)
    protected $id;

    public function __construct(AbstractService $service, $id) {
        $this->setService($service);
        $this->setId($id);
        $this->setUrlTail();
    }

    public function setService(AbstractService $service) {
        $this->service = $service;
        return $this;
    }

    public function getService() {
        return $this->service;
    }

    public function getClient() {
        return $this->getService()->getClient();
    }

    //@return: $this
    public function setUrlTail($urlTail = null) {
        $this->urlTail = $urlTail ? $urlTail : static::SERVER_TAIL;
        return $this;
    }

    public function getUrlTail() {
        return $this->urlTail;
    }

    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    public function getId() {
        return $this->id;
    }

    protected function primaryKeyField() {
        return 'id';
    }

};