<?php

/**
 * SunshineBiz_Location area grid column filter block
 *
 * @category   SunshineBiz
 * @package    SunshineBiz_Location
 * @author     iSunshineTech <isunshinetech@gmail.com>
 * @copyright   Copyright (c) 2013 SunshineBiz.commerce, Inc. (http://www.sunshinebiz.cn)
 */
class SunshineBiz_Location_Block_Area_Grid_Column_Filter_Parent extends Mage_Backend_Block_Widget_Grid_Column_Filter_Select {

    protected function _getOptions() {

        $options = array();
        $value = $this->getColumn()->getGrid()->getColumn('region_id')->getFilter()->getValue();
        if ($value && isset($value['region'])) {
            $options = Mage::getModel('SunshineBiz_Location_Model_Region')
                    ->setId($value['region'])
                    ->getAreas()
                    ->toOptionArray(false);
        }

        array_unshift($options, array(
            'value' => '',
            'label' => ''
        ));

        return $options;
    }

}