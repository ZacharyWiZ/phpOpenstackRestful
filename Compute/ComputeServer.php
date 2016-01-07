<?php
/**
 * Created by PhpStorm.
 * User: wiz
 * Date: 2015/12/18
 * Time: 17:29
 */

require_once("ComputeService.php");
require_once(dirname(__FILE__) . "/../Http/Client.php");
require_once(dirname(__FILE__) . "/../Resource/NovaResource.php");


class ComputeServer extends NovaResource{
    const SERVER_TAIL = "servers";
    const SERVER      = "server";
    const OS_VOL_ATTACH_TAIL = "os-volume_attachments";
    const IMAGE_TAIL  = "images";
    //const FLAVOR_TAIL = "flavors";
    //TODO
    //other function use action and attachVolume

    /**
     * @param : string : ip
     * @return : post response
     */
    public function associateFloatingIp($ip) {
        $floatingIpArr = array(
            "addFloatingIp" => array(
                "address" => $ip
            )
        );
        return parent::action($floatingIpArr);
    }

    public function removeFloatingIp($ip) {
        $removeFloatingIpArr = array(
            "removeFloatingIp" => array(
                "address" => $ip,
            )
        );
        return parent::action($removeFloatingIpArr);
    }

    /**
     * @return floatingIp or null
     */
    public function getFloatingIp() {
        $body = json_decode($this->listDetail(), true);

        if (!array_key_exists(static::SERVER, $body))
            return null;

        foreach ($body[static::SERVER]["addresses"]["admin-net"] as $adminNet ) {
            if ($adminNet["OS-EXT-IPS:type"] == "floating")
                return $adminNet["addr"];
        }
        return null;
    }

    public function attachVolume($volumeId , $device = null) {
        $os_vol_attach_tail = static::OS_VOL_ATTACH_TAIL;
        $tail = '/' . $this->getUrlTail() . '/' . $this->getId() . '/' . $os_vol_attach_tail ;
        $url = $this->getService()->getUrl($tail);

        $attach_arr = array(
            "volumeAttachment" => array(
                "device"   => $device,
                "volumeId" => $volumeId
            )
        );
        $response = json_decode($this->getClient()->post($url, $attach_arr), true);
        if (!array_key_exists("volumeAttachment", $response) || !array_key_exists("id", $response["volumeAttachment"]))
            return null;
        else
            return $response;
    }

    public function detachVolume($attachmentId) {
        $os_vol_attach_tail = static::OS_VOL_ATTACH_TAIL;
        $tail = '/' . $this->getUrlTail() . '/' . $this->getId() . '/' . $os_vol_attach_tail .'/'. $attachmentId;
        $url = $this->getService()->getUrl($tail);
        return $this->getClient()->delete($url);
    }

    /*
    public function imageList() {
        $image_tail = '/' . static::IMAGE_TAIL;
        $url = $this->getService()->getUrl($image_tail);
        return $this->getClient()->get($url);
    }

    public function getImageRef($imageName) {
        $response = $this->ImageList();

        $body = json_decode($response, true);
        foreach($body["images"] as $image){
            if ($image["name"] == $imageName)
                return $image["id"];
        }
        return null;
    }
    */
    /*
    public function flavorList() {
        $flavor_tail = '/' . static::FLAVOR_TAIL .'/' .static::DETAIL;
        $url = $this->getService()->getUrl($flavor_tail);
        return $this->getClient()->get($url);
    }*/

    /**
     * @param $flavorName
     * @return flavorId
     */
    /*
    public function getFlavorRef($flavorName) {
        $body = json_decode($this->FlavorList(), true);
        foreach($body["flavors"] as $flavor) {
            print_r($flavor);
            echo "<br/>";
            if ($flavor["name"] == $flavorName)
                return $flavor["id"];
        }
        return null;
    }*/

    /**
     * @param $propertiesArr  array
     *         e.g. array('ram'   => 255,
     *                    'vcpus' => 2,
     *                    'disk'  => 160(null))
     *         the value of 'disk' can be null
     * @return flavor's id
     */
    /*
    public function findFlavorRefByProperty($propertiesArr) {
        $flavorList = json_decode($this->FlavorList(), true);

        foreach($flavorList["flavors"] as $flavor) {
            if ($flavor["ram"] >= $propertiesArr["ram"] && $flavor["vcpus"] >= $propertiesArr["vcpus"]) {
                if (array_key_exists("disk", $propertiesArr) && $propertiesArr["disk"]!=null) {
                    if ($flavor["disk"] >= $propertiesArr["disk"])
                        return $flavor["id"];
                }else
                    return $flavor["id"];

            }
        }
        return null;
    }*/

    /**
     * The function can starts a stopped server and changes its status to ACTIVE
     *   so precondition was the server status must be SHUTOFF
     */
    public function start() {
        $os_start_arr = array(
           "os-start" => null,
        );
        return $this->action($os_start_arr);
    }

    /**
     * The function can stops a running server and changed its status to SHUTOFF
     *     so precondition was the server status must be ACTIVE or ERROR
     */
    public function stop() {
        $os_stop_arr = array(
            "os-stop" => null,
        );
        return $this->action($os_stop_arr);
    }

    /**
     * The function can resize a server when its status is ACTIVE or SHUTOFF
     * but ,I try to do it cannot success
     *
     * @param $flavorId
     *
     */
    public function resize($flavorId) {
        $resize_arr = array(
            "resize" => array(
                "flavorRef" => $flavorId
            )
        );
        return $this->action($resize_arr);
    }

    public function reboot() {
        $reboot_arr = array(
            "reboot" => array(
                "type" => "HARD"
            )
        );
        return $this->action($reboot_arr);
    }

    public function listNetworkAll() {
        $url = $this->getService()->getUrl('/os-networks');
        return $this->getClient()->get($url);
    }

    public function getNetworkIdByLabel($label) {
        $networkList = json_decode($this->listNetworkAll(), true);
        if (!array_key_exists("networks", $networkList))
            return null;

        foreach($networkList["networks"] as $network) {
            if ($network["label"] == $label)
                return $network["id"];
        }
        return null;
    }

};