<?php
/**
 * Created by PhpStorm.
 * User: wiz
 * Date: 2015/12/22
 * Time: 21:47
 */

require_once("Openstack.php");
require_once("./Common/Endpoint.php");
require_once("./Common/CatalogService.php");
require_once("./Compute/ComputeService.php");

class Drc002 {
    private $computeServer;
    private $volumeServer;

    private $imageRef;
    private $flavorRef;
    private $networkName;
    private $volSize;
    private $device = null;
    private $floatingIp;

    private $computeCreateArr  = array ("server"=>array(
            "name"            => "zq-test-server003",
            "imageRef"        => null,
            "flavorRef"       => null,
            //"key_name"      => "admin-key",
            "networks"        => array(array("uuid" => "b0da0fac-a256-49f7-b79b-c3fd7aaec454")),
            "security_groups" => array(array("name"=>"default")),
            "max_count"       => "1",
            "min_count"       => "1"
        ));

    private $volumeCreateArr  = array ("volume"=>array(
            "status"              => "creating",
            "description"         => null,
            "availability_zone"   => null,
            "source_volid"        => null,
            "consistencygroup_id" => null,
            "snapshot_id"         => null,
            "source_replica"      => null,
            "size"                => null,
            "name"                => "zq-test-vol003",
            "imageRef"            => null,
            "attach_status"       => "detached",
            "volume_type"         => null,
            "project_id"          => null,
            "metadata"            => Array(),
        ));

    public function __construct($url, $secret=array()){
        $client = new Openstack($url, $secret);

        $computeService = $client->computerService();
        $this->computeServer = $computeService->server();

        $volumeService = $client->volumeService();
        $this->volumeServer = $volumeService->server();
    }

    /**
     * @param array $param
     *         e.g. array('ram'   => 255,
     *                    'vcpus' => 2,
     *                    'disk'  => 160(null))
     *         ps : the value of 'disk' can be null
     * @return bool
     */
    public function setFlavorByProperty($param) {
        //$this->flavorRef = $this->getComputeServer()->findFlavorRefByProperty($param);
        $this->flavorRef = $this->getComputeServer()->getService()->flavor()->getFlavorRefByProperty($param);
        if ($this->flavorRef == null)
            return false;
        $this->computeCreateArr["server"]["flavorRef"] = $this->flavorRef;
        return true;
    }

    /**
     * @param array $computeArr
     *           array("name"        => "zq-test-server",
     *                 "imageName"   => "Centos7",
     *                 "networkName" => null)
     *
     * @return json create response
     */
    public function createCompute($computeArr = array()) {
        if (array_key_exists("imageName",$computeArr))
            $this->imageRef = $this->getComputeServer()->getService()->image()->getIdByName($computeArr["imageName"]);
        else if (!array_key_exists("imageName",$computeArr) && $this->computeCreateArr["server"]["imageRef"] == null)
            return null;

        if (array_key_exists("name", $computeArr))
            $this->computeCreateArr["server"]["name"] = $computeArr["name"];

        if (array_key_exists("networkName", $computeArr))
            $this->networkName = $computeArr["networkName"];

        $this->computeCreateArr["server"]["imageRef"] = $this->imageRef;
        return $this->computeServer->create($this->computeCreateArr);
    }

    /**
     * @param array $volumeArr
     *          array("size"   => 1,
     *                "device" => "/dev/sds")
     *
     * @return json create response
     */
    public function createVolume($volumeArr = array()) {
        if (!array_key_exists("size", $volumeArr) && $this->volumeCreateArr["volume"]["size"] == null)
            return null;
        $this->volSize = $volumeArr["size"];
        $this->volumeCreateArr["volume"]["size"] = $this->volSize;

        if (array_key_exists("device", $volumeArr))
            $this->device = $volumeArr["device"];

        $this->volumeCreateArr["volume"]["device"] = $this->device;

        if (array_key_exists("name", $volumeArr))
            $this->volumeCreateArr["volume"]["name"] = $volumeArr["name"];

        return $this->volumeServer->create($this->volumeCreateArr);
    }

    public function setComputeServer($id) {
        $this->getComputeServer()->setId($id);
    }

    public function setVolumeServer($id) {
        $this->getVolumeServer()->setId($id);
    }

    public function associateFloatingIp($ip) {
        $this->floatingIp = $ip;
        return $this->computeServer->associateFloatingIp($this->floatingIp);
    }

    public function removeFloatingIp() {
        if (!isset($this->floatingIp))
            $this->floatingIp = $this->getComputeServer()->getFloatingIp();

        if (!$this->floatingIp)
            return null;

        return $this->computeServer->removeFloatingIp($this->floatingIp);
    }

    public function attachVolume() {
        return $this->computeServer->attachVolume($this->volumeServer->getId(), $this->device);
    }

    public function detachVolume() {
        return $this->computeServer->detachVolume($this->volumeServer->getId());
    }

    public function getComputeServer() {
        return $this->computeServer;
    }

    public function getVolumeServer() {
        return $this->volumeServer;
    }

    public function deleteCompute() {
        return $this->getComputeServer()->delete();
    }

    public function deleteVolume() {
        return $this->getVolumeServer()->delete();
    }


};

set_time_limit(0);

//初始化drc
$url = "http://172.16.4.31";
$secret = array("username"=>"admin",
    "password"=>"jcb410",
    "tenantName"=>"admin",);

$drc = new Drc002($url, $secret);

//根据条件设置flavor
$flavorProperty = array(
    "ram" => "2048",
    "vcpus" => "1",
    "disk"  => "20",
);
if (! $drc->setFlavorByProperty($flavorProperty)) {
    echo "cannot find flavor<br/>";
    return false;
}

//创建虚拟机
$computeArr = array(
    "name"       => "zq-test-drc-003",
    "imageName"  => "DRC-Server-beta01",
);
$drc->createCompute($computeArr);

$computeServer = $drc->getComputeServer();
while($computeServer->getStatus() != "ACTIVE") {
    sleep(20);
}

//创建云硬盘
$volumeArr = array(
    "size"       => 100,
    "device"     => null,
);
$drc->createVolume($volumeArr);

//设置浮动ip
$floatingIp = "172.16.4.111";
print_r($drc->associateFloatingIp($floatingIp));

//挂载云硬盘
$drc->attachVolume();

/*

$computeId = "2080ac4c-60f7-4aec-a1e4-a620e6aa7b19";
$drc->setComputeServer($computeId);

$volumeId = "7616fc78-cf45-418a-abaf-586aa951cc9e";
$drc->setVolumeServer($volumeId);
echo "<br/>floatingip: <br/>";
echo $drc->getComputeServer()->getFloatingIp();
*/