<?php
/**
 * Created by PhpStorm.
 * User: wiz
 * Date: 2015/12/16
 * Time: 16:06
 */

/**
 * This object represents an individual service catalog item - in other words an API Service. Each service has
 * particular information which form the basis of how it distinguishes itself, and how it executes API operations.
 */
class CatalogItem
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $type;

    /**
     * @var array
     */
    private $endpoints = array();

    /**
     * Construct a CatalogItem from a mixed input.
     *
     * @param  $object
     * @return CatalogItem
     */
    public static function factory($object)
    {
        $item = new self();
        $item->setName($object->name)
            ->setType($object->type)
            ->setEndpoints($object->endpoints);

        return $item;
    }

    /**
     * @param $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * A basic string check.
     *
     * @param  $string
     * @return bool
     */
    public function hasName($string)
    {
        return !strnatcasecmp($this->name, $string);
    }

    /**
     * @param $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param $string
     * @return bool
     */
    public function hasType($string)
    {
        return !strnatcasecmp($this->type, $string);
    }

    /**
     * @param  array $endpoints
     * @return $this
     */
    public function setEndpoints(array $endpoints)
    {
        $this->endpoints = $endpoints;

        return $this;
    }

    /**
     * @return array
     */
    public function getEndpoints()
    {
        return $this->endpoints;
    }

    /**
     * Using a standard data object, extract its endpoint.
     *
     * @param string $region
     * @param bool   $isRegionless
     *
     * @return mixed or null
     *
     */
    public function getEndpointFromRegion($region, $isRegionless = false)
    {
        foreach ($this->endpoints as $endpoint) {
            // Return the endpoint if it is regionless OR if the endpoint's
            // region matches the $region supplied by the caller.
            if ($isRegionless || !isset($endpoint->region) || $endpoint->region == $region) {
                return $endpoint;
            }
        }
        return null;
    }
}
