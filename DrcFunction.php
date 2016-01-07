<?php
/**
 * Created by PhpStorm.
 * User: wiz
 * Date: 2015/12/24
 * Time: 20:14
 */

require_once("Openstack.php");
require_once("./Common/Endpoint.php");
require_once("./Common/CatalogService.php");
require_once("./Compute/ComputeService.php");


/***************************************************************
 *  CreateDrcServer($drcServerName, $flavorProperties, $imageName, $networkLabel, $diskSize, $diskAttachPath = null)
 *  DeleteDrcServer($drcServerName)
 *  ResizeDrcServer($drcServerName, $flavorProperties)
 *  AddDisk($drcServerName, $diskSize, $diskAttachPath = null)
 ***************************************************************/


class Drc {

    private $computeServer;
    private $volumeServer;


    public function __construct($url = null, $secret = null) {
        $client = $this->initOpenstack();
        $this->computeServer = $client->computerService()->server();
        $this->volumeServer  = $client->volumeService()->server();
    }

    /**
     * TODO: read the param from one file
     * @param  $url
     * @param $secret (e.g)   array("username"   => "admin",
     *                              "password"   => "jcb410",
     *                              "tenantName" => "admin",)
     *
     * @return Openstack
     */
    public function initOpenstack($url = null, $secret = null) {
        if (!$url)
            $url = "172.16.4.31";
        if (!$secret)
            $secret = array("username"   => "admin",
                "password"   => "jcb410",
                "tenantName" => "admin",);

        return new Openstack($url, $secret);
    }

    /**
     * Create the drc center
     *
     * @param $drcServerName      string
     * @param $flavorProperties   (e.g.) array('ram'   => 255,
     *                                         'vcpus' => 2,
     *                                         'disk'  => 160(null))
     * @param $imageName          string
     * @param $networkLabel       string
     * @param $diskSize           string
     * @param $diskAttachPath     string or null
     *
     * @return null or array("drcServerName"  => $drcServerName,
     *                       "floatingIp"     => $floatingIp,
     *                       "diskAttachPath" => $diskAttachPath);
     */
    public function CreateDrcServer($drcServerName, $flavorProperties, $imageName, $networkLabel,
                                    $diskSize, $diskAttachPath = null) {
        /*
         * create virtual compute
         */
        if ($this->createCompute($drcServerName, $flavorProperties, $imageName, $networkLabel) == null)
            return null;

        /*
         * make sure create compute success
         */
        while ($this->computeServer->getStatus() != "ACTIVE") {
            sleep(5);
            if ($this->computeServer->getStatus() == "ERROR")
                return null;
        }

        /*
         * create a volume
         */
        if ($this->createVolume($diskSize) == null)
            return null;

        /*
         * attach the volume to the compute
         */
        $response = $this->computeServer->attachVolume($this->volumeServer->getId(), $diskAttachPath);
        if (!$response)
            return null;
        $diskAttachPath = $response["volumeAttachment"]["device"];

        /*
         * associate the floating ip to the compute
         */
        $floatingIp = $this->getAvailableFloatingIp($networkLabel);
        $this->computeServer->associateFloatingIp($floatingIp);

        return array("drcServerName"  => $drcServerName,
                          "floatingIp"     => $floatingIp,
                          "diskAttachPath" => $diskAttachPath);

    }

    /**
     * Delete the drc center
     * @param $drcServerName
     * @return bool
     */
    public function DeleteDrcServer($drcServerName) {
        $computeId = $this->computeServer->getIdByName($drcServerName);
        if (!$computeId)
            return false;
        $this->computeServer->setId($computeId);

        $computeDetail = json_decode($this->computeServer->listDetail(), true);
        if (!array_key_exists("server", $computeDetail) ||
            !array_key_exists("os-extended-volumes:volumes_attached", $computeDetail["server"]))
            goto DeleteCompute;

        $volume = $this->volumeServer;
        foreach($computeDetail["server"]["os-extended-volumes:volumes_attached"] as $volumeId) {
            $volume->setId($volumeId);
            $volume->delete();
        }

        DeleteCompute:
        $this->computeServer->delete();
        return true;
    }

    /**
     * @param $drcServerName
     * @param $diskSize
     * @param $diskAttachPath
     * @return null or array(
     *                      "drcServerName"  => $drcServerName,
     *                      "diskAttachPath" => $diskAttachPath );
     */
    public function AddDisk($drcServerName, $diskSize, $diskAttachPath = null) {
        /*
         * set computeServer by drcServerName
         */
        $computeId = $this->computeServer->getIdByName($drcServerName);
        if (!$computeId)
            return null;
        $this->computeServer->setId($computeId);

        /*
         * create a volume
         */
        if ($this->createVolume($diskSize) == null)
            return null;

        /*
         * attach the volume to the compute
         */
        $response = $this->computeServer->attachVolume($this->volumeServer->getId(), $diskAttachPath);
        if (!$response)
            return null;
        $diskAttachPath = $response["volumeAttachment"]["device"];

        return array(
            "drcServerName"  => $drcServerName,
            "diskAttachPath" => $diskAttachPath
        );
    }

    //TODO
    public function ResizeDrcServer($drcServerName, $flavorProperties) {
        return null;
    }


    /**
     * create a virtual compute
     * @param $drcServerName
     * @param $flavorProperties
     * @param $imageName
     * @param $networkLabel
     * @return mixed|null
     */
    private function createCompute($drcServerName, $flavorProperties, $imageName, $networkLabel) {
        $flavorRef = $this->computeServer->getService()->flavor()->getFlavorRefByProperty($flavorProperties);
        if (!$flavorRef)
            return null;

        $imageRef = $this->computeServer->getService()->image()->getIdByName($imageName);
        if (!$imageRef)
            return null;

        $networkId = $this->computeServer->getNetworkIdByLabel($networkLabel);
        if (!$networkId)
            return null;

        $computeCreateArr  = array ("server"=>array(
            "name"            => $drcServerName,
            "imageRef"        => $imageRef,
            "flavorRef"       => $flavorRef,
            //"key_name"      => "admin-key",
            "networks"        => array(array("uuid" => $networkId)),
            "security_groups" => array(array("name"=>"default")),
            //"max_count"       => "1",
            //"min_count"       => "1"
        ));

        $response = json_decode($this->computeServer->create($computeCreateArr), true);
        if (!array_key_exists("server", $response) || !array_key_exists("id", $response["server"]))
            return null;
        else
            return $response;
    }

    private function createVolume($diskSize) {
        $volumeCreateArr  = array ("volume"=>array(
            "status"              => "creating",
            "description"         => null,
            "availability_zone"   => null,
            "source_volid"        => null,
            "consistencygroup_id" => null,
            "snapshot_id"         => null,
            "source_replica"      => null,
            "size"                => $diskSize,
            "name"                => "zq-test-vol",
            "imageRef"            => null,
            "attach_status"       => "detached",
            "volume_type"         => null,
            "project_id"          => null,
            "metadata"            => Array(),
        ));
        $response = json_decode($this->volumeServer->create($volumeCreateArr), true);
        if (!array_key_exists("volume", $response) || !array_key_exists("id", $response["volume"]))
            return null;
        else
            return $response;
    }

    private function getAvailableFloatingIp($networkLabel) {
        $floatingIp = $this->computeServer->getService()->floatingIp();
        $availableIp = $floatingIp->getAvailableIp($networkLabel);
        if ($availableIp != null)
            return $availableIp;
        else
            return $floatingIp->create($networkLabel);
    }

};

set_time_limit(0);
$drc = new Drc();
$flavorProperties = array("ram" => 2048, "vcpus" => 1, "disk"=>20);
$drc->CreateDrcServer("zq-test-drc-S", $flavorProperties, "DRC-Server-beta01", "ext-net", 100);