<?php
/**
 * Created by PhpStorm.
 * User: wiz
 * Date: 2015/12/16
 * Time: 20:20
 */

require_once("Openstack.php");
require_once("./Common/Endpoint.php");
require_once("./Common/CatalogService.php");
require_once("./Compute/ComputeService.php");


$url = "http://172.16.4.31";
$secret = array("username"=>"admin",
                "password"=>"jcb410",
                "tenantName"=>"admin",);

$client = new Openstack($url, $secret);

$compute_service = $client->computerService();
$compute_service->hello();

$server_id = "40fe3e63-7a16-4a42-a302-f8b6e52b851f";
$computeServer = $compute_service->server();

$flavor = $compute_service->flavor();

$image  = $compute_service->image();

$floatingIp = $compute_service->floatingIp();

$create_compute_arr = array(
    "server"=>array(
        "name"            => "zq-test-server34",
        "imageRef"        => "329f4408-d760-4b21-86b6-86d3874106f9",
        "flavorRef"       => "1",
        //"key_name"      => "admin-key",
        "networks"        => array(array("uuid" => "b0da0fac-a256-49f7-b79b-c3fd7aaec454")),
        "security_groups" => array(array("name"=>"default")),
        "max_count"       => "1",
        "min_count"       => "1"
    )
);

$create_volume_arr = array(
    "volume"=>array(
        "status"              => "creating",
        "description"         => null,
        "availability_zone"   => null,
        "source_volid"        => null,
        "consistencygroup_id" => null,
        "snapshot_id"         => null,
        "source_replica"      => null,
        "size"                => "1",
        "name"                => "zq-test-vol_MSY",
        "imageRef"            => null,
        "attach_status"       => "detached",
        "volume_type"         => null,
        "project_id"          => null,
        "metadata"            => Array(),
    ),
);
/********************************************************************************/

$volumeId = "6aec65d0-ef1d-41ca-a2ee-d88b352d568a";
$volumeService = $client->volumeService();
$volumeServer  = $volumeService->server();


echo "<br/><br/><br/><br/>";
echo $compute_service->getBaseUrl();
echo "<br/><br/><br/><br/>";
echo "Status: ";
//echo $volumeServer->getStatus();

echo "<br/><br/><br/><br/>";
//print_r($volumeServer->delete());

echo "<br/><br/><br/><br/>";

echo "computeServer->listNetwork: <br/>";
print_r($computeServer->listNetworkAll());

echo "<br/><br/><br/><br/>";


echo "<br/><br/><br/><br/>";
echo "get networkid:<br/>";
echo $computeServer->getNetworkIdByLabel("admin-net") . "<br/>";

