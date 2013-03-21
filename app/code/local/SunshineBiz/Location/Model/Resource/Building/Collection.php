<?php

/**
 * SunshineBiz_Location building collection
 *
 * @category   SunshineBiz
 * @package    SunshineBiz_Location
 * @author     iSunshineTech <isunshinetech@gmail.com>
 * @copyright   Copyright (c) 2013 SunshineBiz.commerce, Inc. (http://www.sunshinebiz.cn)
 */
class SunshineBiz_Location_Model_Resource_Building_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract {

    protected $_buildingNameTable;

    /**
     * Resource initialization
     */
    protected function _construct() {
        $this->_init('SunshineBiz_Location_Model_Building', 'SunshineBiz_Location_Model_Resource_Building');
        $this->_buildingNameTable = $this->getTable('location_building_name');

        $this->addOrder('area_id', Varien_Data_Collection::SORT_ORDER_DESC);
    }

    protected function _initSelect() {

        parent::_initSelect();
        $locale = Mage::app()->getLocale()->getLocaleCode();
        $this->addBindParam(':building_locale', $locale);
        $this->getSelect()->joinLeft(
                array('bname' => $this->_buildingNameTable), 'main_table.id = bname.building_id AND bname.locale = :building_locale', array('name'));

        return $this;
    }

}