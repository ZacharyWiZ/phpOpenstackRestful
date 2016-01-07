<?php
/**
 * Created by PhpStorm.
 * User: wiz
 * Date: 2015/12/22
 * Time: 20:28
 */

/**
 * 用户使用说明：
 */

/**
 * 首先必须指定url
 */
$url = "172.16.4.31";

/**
 * 必须指定username, password, tenantName*(必须知道租户的名称)
 */
$secret = array(
    "username"   => "admin",
    "password"   => "jcb410",
    "tenantName" => "admin",);

/**
 * 接着创建Openstack实例
 */

$client = new Openstack($url, $secret);

/**
 * 然后由Openstack类创建 Service实例： 这里只实现了computeService, volumeService
 *当然必要时需要说明Service的$name, $region, $urlType(e.g. 'publicURL' or 'internalURL'.)
 */
$computeService = $client->computerService();
$volumeService  = $client->volumeService();

/**
 *接下来以computerServer为例子：
 *  创建虚拟机：
 *  这里在server($computeId)方法中不需要指定computeId
 */
$computeServer = $compute_service->server();
/**
 * 得到所有虚拟机的信息
 * 返回：json格式
 */
$computeServer->listDetailAll();

/**
 * 创建虚拟机
 */
$create_compute_arr = array(
    "server"=>array(
        "name"            => "zq-test-server3",
        "imageRef"        => "329f4408-d760-4b21-86b6-86d3874106f9",
        "flavorRef"       => "1",
        //"key_name"      => "admin-key",
        "networks"        => array(array("uuid" => "b0da0fac-a256-49f7-b79b-c3fd7aaec454")),
        "security_groups" => array(array("name"=>"default")),
        "max_count"       => "1",
        "min_count"       => "1"
    )
);


$computeServer->create($create_compute_arr);

