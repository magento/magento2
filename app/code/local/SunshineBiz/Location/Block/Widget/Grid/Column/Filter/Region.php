<?php

/**
 * @category   SunshineBiz
 * @package    SunshineBiz_Location
 * @author     iSunshineTech <isunshinetech@gmail.com>
 * @copyright   Copyright (c) 2013 SunshineBiz.commerce, Inc. (http://www.sunshinebiz.cn)
 */
class SunshineBiz_Location_Block_Widget_Grid_Column_Filter_Region extends Mage_Backend_Block_Widget_Grid_Column_Filter_Select {

    protected function _getOptions() {

        $options = array();
        $value = $this->getData('value');
        if (isset($value['country'])) {
            $options = Mage::getModel('Mage_Directory_Model_Country')
                    ->setId($value['country'])
                    ->getRegions()
                    ->toOptionArray(false);
        }

        return $options;
    }

    protected function _getCountryOptions() {
        $options = Mage::getResourceModel('Mage_Directory_Model_Resource_Country_Collection')
                ->load()
                ->toOptionArray(Mage::helper('Mage_Backend_Helper_Data')->__('All Countries'));

        return $options;
    }

    public function getHtml() {
        $value = $this->getData('value');
        $html = '<div><select name="' . $this->_getHtmlName() . '[country]" id="' . $this->_getHtmlId() . '_country" class="no-changes" onChange="locationChanged(this, \'' . $this->getUrl('*/json/countryRegion') . '\',  \'' . $this->_getHtmlId() . '_region\')">';
        foreach ($this->_getCountryOptions() as $option) {
            $html .= $this->_renderOption($option, isset($value['country']) ? $value['country'] : '');
        }
        $html .='</select></div>';

        $html .= '<div><select name="' . $this->_getHtmlName() . '[region]" id="' . $this->_getHtmlId() . '_region" class="no-changes" onChange="locationChanged(this, \'' . $this->getUrl('*/json/regionArea') . '\',  \'locationsAreaGrid_filter_parent_id\')">';
        foreach ($this->_getOptions() as $option) {
            $html .= $this->_renderOption($option, isset($value['region']) ? $value['region'] : '');
        }
        $html.='</select></div>';

        return $html;
    }

    public function getCondition() {
        $value = $this->getData('value');

        if (isset($value['country'])) {
            if (isset($value['region'])) {
                return array('eq' => $value['region']);
            } else {
                $options = Mage::getModel('Mage_Directory_Model_Country')
                        ->setId($value['country'])
                        ->getRegions();
                if (count($options) > 0) {
                    $regions = array();
                    foreach ($options as $option) {
                        $regions[] = $option['region_id'];
                    }

                    return array('in' => $regions);
                }
            }
        }

        return null;
    }

}