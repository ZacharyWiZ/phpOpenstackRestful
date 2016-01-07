<?php
/**
 * Created by PhpStorm.
 * User: wiz
 * Date: 2015/12/16
 * Time: 15:49
 */

require_once("./Http/Client.php");
require_once("./Common/Catalog.php");
require_once("./Common/CatalogItem.php");
require_once("./Compute/ComputeService.php");
require_once("./Volume/VolumeService.php");
require_once("./Identity/IdentityService.php");

class Openstack extends Client {
    /**
     * @var array = {"username"=>"admin", "password"=>"jcb410"}
     */
    private $secret = array();

    //@var string : e4ca38ebf28c4cf6a0f6f8ae32233726
    private $token = null;

    //@var string : tenant_id as e4ca38ebf28c4cf6a0f6f8ae32233726
    private $tenant = null;

    //@var object: \Service\Catalog
    private $catalog = null;

    //@var string (e.g. http://172.16.4.31)
    private $authUrl = null;

    //@var string (e.g. http://172/16/4/31/v2/16466sdfasdf68484/)
    //private $baseUrl = null;

    //@var string (e.g. 5000)
    //private $tokenPort = null;
    //TODO:
    //private $user;

    /**
     * param: $secret must have key: "username" and "password"
     */
    public function __construct($url, array $secret) {
        if (!array_key_exists("username",$secret) || !array_key_exists("password", $secret)) {
            echo "Openstack: __construct has err param: $secret";
            return null;
        }

        $this->setSecret($secret);
        $this->setAuthUrl($url);//这里只有纯ip

        parent::__construct();

        echo "<br/>Openstack __construct() ok <br/>";
    }

    /**
     * Set the credentials for the client
     *
     * @param array $secret
     * @return $this
     */
    public function setSecret(array $secret = array()) {
        $this->secret = $secret;

        return $this;
    }

    /**
     * Get the secret.
     *
     * @return array
     */
    public function getSecret() {
        return $this->secret;
    }

    public function setToken($token) {
        $this->token = $token;
        return $this;
    }

    public function getToken() {
        return $this->token;
    }

    public function setTenant($tenant) {
        $this->tenant = $tenant;
        return $this;
    }

    public function getTenant() {
        return $this->tenant;
    }

    /**
     * @param: response(json) by http
     * @return $this
     */
    public function setCatalog($catalog) {
        $this->catalog = Catalog::factory($catalog);
        return $this;
    }

    public function getCatalog() {
        return $this->catalog;
    }

    //return $this
    public function setAuthUrl($url) {
        $this->authUrl = $url;
        return $this;
    }

    public function getAuthUrl() {
        return $this->authUrl;
    }

    /*
    //return $this
    public function setTokenPort($port = "5000") {
        $this->tokenPort = $port;
        return $this;
    }

    public function getTokenPort() {
        return $this->TokenPort;
    }
    */

    /**
     * Formats the credentials array (as a string) for authentication
     *
     * @return string
     */
    public function getCredentials() {
        if (!empty($this->secret['username']) && !empty($this->secret['password'])) {
            $credentials = array('auth' => array(
                'passwordCredentials' => array(
                    'username' => $this->secret['username'],
                    'password' => $this->secret['password']
                )
            ));

            if (!empty($this->secret['tenantName'])) {
                $credentials['auth']['tenantName'] = $this->secret['tenantName'];
            } elseif (!empty($this->secret['tenantId'])) {
                $credentials['auth']['tenantId'] = $this->secret['tenantId'];
            }

            return json_encode($credentials);
        }else  {
            echo "Openstack : getCredential: err secret";
            return null;
        }
    }

    /**
     * Authenticate the tenant using the supplied credentials
     * and we will update X-Auth-Token in here.
     * @return null
     */
    public function authenticate() {
        //TODO
        /*$client = new Client();
        $client->setURL("http://172.16.4.31:5000/v2.0/tokens");
        $response = $client->post_json($this->getCredentials());
        */

        $identity = IdentityService::factory($this);
        $response = $identity->generateToken($this->getCredentials());

        $body = json_decode($response);

        //echo "access->serviceCatalog:<br/>";
        //print_r($body);
        //echo "<br/>";
        $this->setCatalog($body->access->serviceCatalog);
        $this->setToken($body->access->token->id);
        parent::setHeader("X-Auth-Token", $this->getToken());

        if (isset($body->access->token->tenant)) {
            $this->setTenant($body->access->token->tenant->id);
        }
    }

    //factory
    public function computerService($name = null, $region = null, $urlType = null) {
        $type = null;
        return new ComputeService($this ,$type, $name, $region, $urlType);
    }

    //factory
    public function volumeService($name = null, $region = null, $urlType = null) {
        $type = null;
        return new VolumeService($this, $type, $name, $region, $urlType);
    }


};