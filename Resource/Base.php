<?php
/**
 * Created by PhpStorm.
 * User: wiz
 * Date: 2015/12/22
 * Time: 14:38
 */

class Base {

    /**
     * Holds all the properties added by overloading
     *
     * @var array
     */
    private $properties = array();

    public function getInstance() {
        return new static();
    }

    /**
     * there are wizard function
     */
    public function __set($name, $value) {
        echo "<br/>__set: $name, $value<br/>";
        $this->properties[$name] = $value;
    }

    public function __get($name) {
        echo "<br/>__get: $name<br/>";
        if (array_key_exists($name, $this->properties))
            return $this->properties[$name];
        else
            return null;
    }

    public function __isset($name) {
        return isset($this->properties[$name]);
    }

    public function __unset($name) {
        unset($this->properties[$name]);
    }

    /**
     * wizard function
     * @param $method: the class don't has the function name:method
     *                and $method e.g. : setName or getName
     *
     * @param $args
     *
     *
     */
    public function __call($method, $args) {
        $prefix = substr($method , 0, 3);
        $property = lcfirst(substr($method, 3));

        if (property_exists($this, $property) && $prefix == 'get') {
            return $this->getProperty($property);
        }

        if (property_exists($this, $property) && $prefix == 'set') {
            //return $this->setProperty($property, $args);
        }
    }

    protected function getProperty($property) {
        if (array_key_exists($property, $this->properties)) {
            return $this->properties[$property];
        }else if (method_exists($this, 'get' . ucfirst($property))) {
            return call_user_func(array($this, 'get' . ucfirst($property)));
        }else if ($this->isAccessible($property)) {
            return $this->$property;
        }else
            return null;
    }

    protected function setProperty($property, $value) {
        $setter = 'set' . ucfirst($property);

        if (method_exists($this, $setter)) {
            return call_user_func(array($this, $setter), $value);
        }else if ($this->isAccessible($property)) {
            $this->$property = $value;
            return $this;
        }else if (array_key_exists($property, $this->properties)) {
            $this->properties[$property] = $value;
            return $this;
        }

        return null;
    }

    private function isAccessible($property) {
        return array_key_exists($property, get_object_vars($this));
    }

    public function populate($info) {

    }

};

$base = new Base();
$base->msy = "heihei";
echo "propertyExists: ";
print_r($base->propertyExists("msy"));
print_r($base);
$e = false;
echo "<br/>echo e: $e<br/>";