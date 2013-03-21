<?php

/**
 * SunshineBiz_Location region block
 *
 * @category   SunshineBiz
 * @package    SunshineBiz_Location
 * @author     iSunshineTech <isunshinetech@gmail.com>
 * @copyright   Copyright (c) 2013 SunshineBiz.commerce, Inc. (http://www.sunshinebiz.cn)
 */
class SunshineBiz_Location_Block_Region extends SunshineBiz_Location_Block_Widget_Grid_Container {

    public function _construct() {
        $this->_controller = 'region';
        $this->_headerText = Mage::helper('SunshineBiz_Location_Helper_Data')->__('Regions');
        $this->_addButtonLabel = Mage::helper('SunshineBiz_Location_Helper_Data')->__('Add New Region');
        parent::_construct();
    }
}