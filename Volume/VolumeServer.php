<?php
/**
 * Created by PhpStorm.
 * User: wiz
 * Date: 2015/12/19
 * Time: 22:01
 */

require_once("VolumeService.php");
require_once(dirname(__FILE__) . "/../Http/Client.php");
require_once(dirname(__FILE__) . "/../Resource/NovaResource.php");

class VolumeServer extends NovaResource {
    const SERVER_TAIL = "volumes";
    const SERVER      = "volume";


    //TODO : some function


};