<?php
/**
 * Created by PhpStorm.
 * User: wiz
 * Date: 2015/12/22
 * Time: 20:28
 */

/**
 * �û�ʹ��˵����
 */

/**
 * ���ȱ���ָ��url
 */
$url = "172.16.4.31";

/**
 * ����ָ��username, password, tenantName*(����֪���⻧������)
 */
$secret = array(
    "username"   => "admin",
    "password"   => "jcb410",
    "tenantName" => "admin",);

/**
 * ���Ŵ���Openstackʵ��
 */

$client = new Openstack($url, $secret);

/**
 * Ȼ����Openstack�ഴ�� Serviceʵ���� ����ֻʵ����computeService, volumeService
 *��Ȼ��Ҫʱ��Ҫ˵��Service��$name, $region, $urlType(e.g. 'publicURL' or 'internalURL'.)
 */
$computeService = $client->computerService();
$volumeService  = $client->volumeService();

/**
 *��������computerServerΪ���ӣ�
 *  �����������
 *  ������server($computeId)�����в���Ҫָ��computeId
 */
$computeServer = $compute_service->server();
/**
 * �õ��������������Ϣ
 * ���أ�json��ʽ
 */
$computeServer->listDetailAll();

/**
 * ���������
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

