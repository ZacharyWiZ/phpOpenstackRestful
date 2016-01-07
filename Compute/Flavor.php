<?php
/**
 * Created by PhpStorm.
 * User: wiz
 * Date: 2015/12/23
 * Time: 19:51
 */
require_once(dirname(__FILE__) . "/../Resource/PersistentResource.php");

class Flavor extends PersistentResource {

    const SERVER_TAIL = "flavors";
    const SERVER      = "flavor";
    //const DETAIL      = "detail";
    //const OS_FLAVOR_MANAGE = "os-flavor-manage";

    /***************************************************************
     *  class Flavor work function
     * create / delete / listDetail /listDetailAll
     ***************************************************************/

    /**
     * @param  array
     *        e.g. array(
     *          "name": "test_flavor",
     *          "ram": 1024,
     *          "vcpus": 2,
     *          "disk": 10,
     *          "id": "10"
     *      )
     *      public function create
     *
     */

    /**
     * @param $propertiesArr
     *         e.g. array('ram'   => 255,
     *                    'vcpus' => 2,
     *                    'disk'  => 160(null))
     *         the value of 'disk' can be null
     * @return flavor's id
     */
    public function getFlavorRefByProperty($propertiesArr) {
        $flavorList = json_decode($this->listDetailAll(), true);

        foreach($flavorList["flavors"] as $flavor) {
            if ($flavor["ram"] == $propertiesArr["ram"] && $flavor["vcpus"] == $propertiesArr["vcpus"]) {
                if (array_key_exists("disk", $propertiesArr) && $propertiesArr["disk"]!=null) {
                    if ($flavor["disk"] == $propertiesArr["disk"])
                        return $flavor["id"];
                }else
                    return $flavor["id"];

            }
        }
        return null;
    }

}