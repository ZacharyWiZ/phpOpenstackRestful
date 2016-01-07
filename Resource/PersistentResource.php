<?php
/**
 * Created by PhpStorm.
 * User: wiz
 * Date: 2015/12/18
 * Time: 17:48
 */

require_once("BaseResource.php");

abstract class PersistentResource  extends BaseResource{

    const DETAIL = "detail";

    public function create($tail = null, $param = array()) {
        if (is_array($tail)) {
            $param = $tail;
            $tail = "/" . $this->getUrlTail();
        }

        echo "<br/>json_create<br/>";
        print_r(json_encode($param));
        echo "<br/>";

        $url = $this->getService()->getUrl($tail);

        $response = $this->getClient()->post($url, $param);
        $body = json_decode($response, true);

        $this->setId($body[static::SERVER]["id"]);
        return $response;
    }

    //update the token
    public function updateAuthenticate() {
        $this->getClient()->authenticate();
    }

    //TODO: put function we donot test is not ok
    //url should tail with update id
    public function update($param = array()) {
        $tail = '/' . $this->getUrlTail() . '/' . $this->getId();

        $url = $this->getService()->getUrl($tail);
        return $this->getClient()->put($url, $param);
    }

    public function delete() {
        $tail = '/' . $this->getUrlTail() . '/'.$this->getId();
        $url = $this->getService()->getUrl($tail);
        return $this->getClient()->delete($url);
    }

    //image has no detail in the url
    //so we must set DETAIL=null
    public function listDetailAll($info = null) {
        $detail = static::DETAIL;

        $tail = '/' . $this->getUrlTail() . '/' . $detail;
        if ($info) {
            $tail = $tail . '?' . $info;
        }

        $url = $this->getService()->getUrl($tail);
        return $this->getClient()->get($url);
    }

    public function listDetail() {
        $tail = '/' . $this->getUrlTail() . '/' . $this->getId();
        $url = $this->getService()->getUrl($tail);
        return $this->getClient()->get($url);
    }

    /**
     * @param $name
     * @return string $id or null
     */
    public function getIdByName($name) {
        $resourceList = json_decode($this->listDetailAll(), true);
        if (!array_key_exists(static::SERVER_TAIL, $resourceList))
            return null;
        foreach($resourceList[static::SERVER_TAIL] as $resource) {
            if ($resource["name"] == $name)
                return $resource["id"];
        }
        return null;
    }

    public function getStatus() {
        if (!isset($this->id))
            return null;

        $detail = json_decode($this->listDetail(), true);

        if (array_key_exists(static::SERVER , $detail))
            return $detail[static::SERVER]["status"];
        else
            return null;
    }

};