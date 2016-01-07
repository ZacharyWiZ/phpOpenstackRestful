<?php
/**
 * Created by PhpStorm.
 * User: wiz
 * Date: 2015/12/19
 * Time: 16:05
 */

require_once("PersistentResource.php");

abstract class NovaResource extends PersistentResource {
    const ACTION = "action";
    //const DETAIL = "detail";

    /**
     * I just see action in compute / volumes server
     *
     * @param array $param
     */
    public function action($param = array()) {
        $action = static::ACTION;
        $tail = '/' . $this->getUrlTail() . '/' . $this->getId() . '/' . $action;
        $url = $this->getService()->getUrl($tail);
        return $this->getClient()->post($url, $param);
    }
}