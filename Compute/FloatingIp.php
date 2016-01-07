<?php
/**
 * Created by PhpStorm.
 * User: wiz
 * Date: 2015/12/24
 * Time: 15:35
 */
require_once(dirname(__FILE__) . "/../Resource/BaseResource.php");

class FloatingIp extends BaseResource {
    const SERVER_TAIL = "os-floating-ips";
    const FLOATING_IP_POOLS = "os-floating-ip-pools";
    const FLOATING_IPS = "floating_ips";
    /************************************************************
     * class FloatingIp
     *
     * getFloatingIpPools() @return   array( "floating_ip_pools" => array("name" => "ext-net"))
     *
     * create() @return string ip
     *
     * delete($ip)
     *
     * listDetailAll()
     ***********************************************************/

    public function getFloatingIpPools() {
        $url = $this->getService()->getUrl('/' . static::FLOATING_IP_POOLS);
        return $this->getClient()->get($url);
    }

    public function listDetailAll() {
        $url = $this->getService()->getUrl('/' . static::SERVER_TAIL);
        return $this->getClient()->get($url);
    }

    public function getAvailableIp($pool = null) {
        $floatingIpList = json_decode($this->listDetailAll(), true);
        if (!array_key_exists(static::FLOATING_IPS, $floatingIpList))
            return null;
        foreach ($floatingIpList[static::FLOATING_IPS] as $floatingIp) {
            if ($floatingIp["instance_id"] == null) {
                if (!$pool)
                    return $floatingIp["ip"];
                else if ($floatingIp["pool"] == $pool)
                    return $floatingIp["ip"];
            }
        }
        return null;
    }

    /**
     * @param $ipPool (e.g. ext-net)
     * @return string the new ip  or null
     */
    public function create($ipPool) {
        $url = $this->getService()->getUrl('/' . static::SERVER_TAIL);
        $createFloatingIpArr = array(
            "pool" => $ipPool
        );
        $body = json_decode($this->getClient()->post($url, $createFloatingIpArr), true);

        print_r($body);

        if (!array_key_exists("floating_ip", $body))
            return null;
        else if (!array_key_exists("ip", $body["floating_ip"]))
            return null;
        else
            return $body["floating_ip"]["ip"];
    }

    public function getIdByIp($ip) {
        $floatingIpList = json_decode($this->listDetailAll(), true);
        if (!array_key_exists(static::FLOATING_IPS, $floatingIpList))
            return null;
        foreach ($floatingIpList[static::FLOATING_IPS] as $floatingIp) {
            if ($floatingIp["ip"] == $ip)
                return $floatingIp["id"];
        }
        return null;
    }

    /**
     * @param $ip
     * @return null or json
     */
    public function delete($ip) {
        $id = $this->getIdByIp($ip);

        if (!$id)
            return null;
        $url_tail = '/' . static::SERVER_TAIL . '/' . $id;
        $url = $this->getService()->getUrl($url_tail);
        return $this->getClient()->delete($url);
    }
}