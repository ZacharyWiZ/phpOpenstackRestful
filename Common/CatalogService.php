<?php
/**
 * Created by PhpStorm.
 * User: wiz
 * Date: 2015/12/17
 * Time: 16:35
 */

require_once(dirname(__FILE__)."/../Http/Client.php");
require_once("AbstractService.php");
require_once(dirname(__FILE__) . "/../Identity/IdentityService.php");

abstract class CatalogService extends AbstractService {

    const DEFAULT_URL_TYPE = 'publicURL';
    /**
     * @var string The type of this service, as set in Catalog.
     */
    private $type;

    /**
     * @var string The name of this service, as set in Catalog.
     */
    private $name;

    /**
     * @var string The chosen region(s) for this service.
     */
    private $region;

    /**
     * @var string Either 'publicURL' or 'internalURL'.
     */
    private $urlType;

    /**
     * @var bool Indicates whether a service is "regionless" or not. Defaults to FALSE because nearly all services
     *           are region-specific.
     */
    protected $regionless = true;

    private $url;

    /**
     * Creates a service object
     *
     * @param Client
     * @param $type string (e.g. 'compute')
     * @param $name string (e.g. 'nova')
     * @param $region string (e.g. 'RegionOne')
     * @param $urlType string (e.g. 'publicURL' / 'adminURL' / 'interalURL')
     */
    public function __construct(Client $client, $type = null, $name = null, $region = null, $urlType = null) {

        $this->setClient($client);

        $this->name = $name ? : static::DEFAULT_NAME;
        $this->regoin = $region;
        $this->type = $type ? : static::DEFAULT_TYPE;
        $this->urlType = $urlType ? : static::DEFAULT_URL_TYPE;

        $this->setEndpoint($this->findEndpoint());
        $this->url = $this->getBaseUrl();
    }

    /**
     * @return string
     */
    public function getType() {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getRegion() {
        return $this->region;
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getUrlType() {
        return $this->urlType;
    }

    //TODO
    public function getUrl($path = null) {
        return $this->url  . $path;
    }

    public function getBaseUrl() {
        $url = null;
        switch($this->urlType) {
            case 'publicURL':
                echo "<br/>CatalogService:<br/>";
                print_r($this->endpoint);
                $url = $this->endpoint->getPublicUrl();
                break;
            case 'internalURL':
                $url = $this->endpoint->getInternalUrl();
                break;
            case 'adminURL':
                $url = $this->endpoint->getAdminUrl();
                break;
            default:
                $url = null;
        }

        return $url;
    }

    //@return Service/Endpont
    private function findEndpoint() {
        if (!$this->getClient()->getCatalog()) {
            $this->getClient()->authenticate();
        }

        $catalog = $this->getClient()->getCatalog();
        echo "Catalog:<br/>";
        print_r($catalog);
        //echo "<br/>Catalog->getItems():<br/>";
        //print_r($catalog->getItems());
        // Search each service to find The One
        foreach ($catalog->getItems() as $service) {
            if ($service->hasType($this->type) && $service->hasName($this->name)) {
                $endpoint = $service->getEndpointFromRegion($this->region, $this->regionless);
                return Endpoint::factory($endpoint, $this->getClient());
            }
        }
        echo "<br/>CatalogService findEndpoint : no endpoint.<br/>";
        return null;
    }
};