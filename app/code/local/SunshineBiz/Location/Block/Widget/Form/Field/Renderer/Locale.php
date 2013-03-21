<?php

/**
 * SunshineBiz_Location locale field renderer
 *
 * @category   SunshineBiz
 * @package    SunshineBiz_Location
 * @author     iSunshineTech <isunshinetech@gmail.com>
 * @copyright   Copyright (c) 2013 SunshineBiz.commerce, Inc. (http://www.sunshinebiz.cn)
 */
class SunshineBiz_Location_Block_Widget_Form_Field_Renderer_Locale extends Mage_Backend_Block_Abstract implements Varien_Data_Form_Element_Renderer_Interface {

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element) {
        return $element->getElementHtml();
    }

    public function render(Varien_Data_Form_Element_Abstract $element) {

        $required = $element->getRequired() ? ' required' : '';
        $fieldAttributes = ' class="field field-' . $element->getHtmlId() . $required . '" ' . $this->getUiId('form-field', $element->getHtmlId());
        $addInheritCheckbox = $element->getCanUseDefaultValue();

        if ($element->getInherit() && $addInheritCheckbox) {
            $element->setDisabled(true);
        }
        
        $html = "<div {$fieldAttributes}>";
        $html .= $element->getLabelHtml();
        $html .= '<div class="control">';
        $html .= $element->getElementHtml();
        $html .= '</div>';
        if ($addInheritCheckbox) {
           $html .= $this->_renderInheritCheckbox($element);
        }
        $html .= '<div class="scope-label" style="display: inline-block; padding: 5px 5px;">' . $element->getScopeLabel() . '</div>';
        $html .= '</div>';
        
        return $html;
    }
    
    protected function _renderInheritCheckbox(Varien_Data_Form_Element_Abstract $element) {
        
        $htmlId = $element->getHtmlId();
        $namePrefix = preg_replace('#\[value\](\[\])?$#', '', $element->getName());
        $checkedHtml = $element->getInherit() ? 'checked="checked"' : '';         
         $checkboxLabel = Mage::helper('Mage_Adminhtml_Helper_Data')->__('Use Default');
         
         $html = '<div class="use-default" style="display: inline-block; padding: 5px 5px;">';
         $html .= '<input id="' . $htmlId . '_inherit" name="' . $namePrefix . '_inherit" type="checkbox" value="1" class="checkbox config-inherit" ' . $checkedHtml . ' onclick="toggleValueElements(this, Element.previous(this.parentNode))" /> ';
         $html .= '<label for="' . $htmlId . '_inherit" class="inherit">' . $checkboxLabel . '</label>';
         $html .= '</div>';
         
         return $html;
    }
}