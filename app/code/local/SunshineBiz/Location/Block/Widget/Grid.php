<?php

/**
 * @category   SunshineBiz
 * @package    SunshineBiz_Location
 * @author     iSunshineTech <isunshinetech@gmail.com>
 * @copyright   Copyright (c) 2013 SunshineBiz.commerce, Inc. (http://www.sunshinebiz.cn)
 */
class SunshineBiz_Location_Block_Widget_Grid extends Mage_Backend_Block_Widget_Grid_Extended {
    
    protected $_helper;
    
    public function _construct() {
        parent::_construct();
        $this->_helper = $this->helper('SunshineBiz_Location_Helper_Data');
    }
}