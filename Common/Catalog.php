<?php
/**
 * Created by PhpStorm.
 * User: wiz
 * Date: 2015/12/16
 * Time: 15:58
 */


class Catalog {
    //@var array Service items
    private $items = array();

    /*
     * @param  $config(get from http response)
     * @return Catalog
     */
    public static function factory($config) {
        if (is_array($config)) {
            $catalog = new self();
            foreach ($config as $item) {
                $catalog->items[] = CatalogItem::factory($item);
            }
        }else if($config instanceof Catalog) {
            $catalog = $config;
        }else {
            echo "Catalog->factory error param";
            return null;
        }
        return $catalog;
    }

    /*
     * @return array
     */
    public function getItems() {
        return $this->items;
    }
};