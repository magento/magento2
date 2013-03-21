<?php

/**
 * SunshineBiz_Location Region collection
 *
 * @category   SunshineBiz
 * @package    SunshineBiz_Location
 * @author     iSunshineTech <isunshinetech@gmail.com>
 * @copyright   Copyright (c) 2013 SunshineBiz.commerce, Inc. (http://www.sunshinebiz.cn)
 */
class SunshineBiz_Location_Model_Resource_Region_Collection extends Mage_Directory_Model_Resource_Region_Collection {

    protected function _construct() {
        $this->_init('SunshineBiz_Location_Model_Region', 'SunshineBiz_Location_Model_Resource_Region');

        $this->_countryTable = $this->getTable('directory_country');
        $this->_regionNameTable = $this->getTable('directory_country_region_name');
    }

    public function toOptionArray($emptyLabel = ' ') {

        $options = array();
        foreach ($this as $region) {
            $options[] = array(
                'value' => $region->getId(),
                'label' => $region->getName()
            );
        }

        if (count($options) > 0 && $emptyLabel !== false) {
            array_unshift($options, array('value' => '', 'label' => $emptyLabel));
        }

        return $options;
    }

}