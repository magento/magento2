<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Backend
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Config form fieldset renderer
 *
 * @category   Mage
 * @package    Mage_Backend
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Backend_Block_System_Config_Form_Fieldset
    extends Mage_Backend_Block_Abstract
    implements Varien_Data_Form_Element_Renderer_Interface
{

    /**
     * Render fieldset html
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $html = $this->_getHeaderHtml($element);

        foreach ($element->getSortedElements() as $field) {
            $html .= $field->toHtml();
        }

        $html .= $this->_getFooterHtml($element);

        return $html;
    }

    /**
     * Return header html for fieldset
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getHeaderHtml($element)
    {
        $default = !$this->getRequest()->getParam('website') && !$this->getRequest()->getParam('store');

        $html = '<div  class="entry-edit-head collapseable" ><a id="' . $element->getHtmlId()
            . '-head" href="#" onclick="Fieldset.toggleCollapse(\'' . $element->getHtmlId() . '\', \''
            . $this->getUrl('*/*/state') . '\'); return false;">' . $element->getLegend() . '</a></div>';
        $html .= '<input id="'.$element->getHtmlId() . '-state" name="config_state[' . $element->getId()
            . ']" type="hidden" value="' . (int)$this->_getCollapseState($element) . '" />';
        $html .= '<fieldset class="' . $this->_getFieldsetCss() . '" id="' . $element->getHtmlId() . '">';
        $html .= '<legend>' . $element->getLegend() . '</legend>';

        if ($element->getComment()) {
            $html .= '<span class="comment" style="display: block;">' . $element->getComment() . '</span>';
        }
        // field label column
        $html .= '<table cellspacing="0" class="form-list"><colgroup class="label" /><colgroup class="value" />';
        if (!$default) {
            $html .= '<colgroup class="use-default" />';
        }
        $html .= '<colgroup class="scope-label" /><colgroup class="" /><tbody>';

        return $html;
    }

    /**
     * Return full css class name for form fieldset
     *
     * @return string
     */
    protected function _getFieldsetCss()
    {
        $group = $this->getGroup();
        $configCss = isset($group['fieldset_css']) ? $group['fieldset_css'] : null;
        return 'config collapseable' . ($configCss ? ' ' . $configCss : '');
    }

    /**
     * Return footer html for fieldset
     * Add extra tooltip comments to elements
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getFooterHtml($element)
    {
        $tooltipsExist = false;
        $html = '</tbody></table>';
        foreach ($element->getSortedElements() as $field) {
            if ($field->getTooltip()) {
                $tooltipsExist = true;
                $html .= sprintf('<div id="row_%s_comment" class="system-tooltip-box" style="display:none;">%s</div>',
                    $field->getId(), $field->getTooltip()
                );
            }
        }
        $html .= '</fieldset>' . $this->_getExtraJs($element, $tooltipsExist);
        return $html;
    }

    /**
     * Return js code for fieldset:
     * - observe fieldset rows;
     * - apply collapse;
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @param bool $tooltipsExist Init tooltips observer or not
     * @return string
     */
    protected function _getExtraJs($element, $tooltipsExist = false)
    {
        $id = $element->getHtmlId();
        $js = "Fieldset.applyCollapse('{$id}');";
        if ($tooltipsExist) {
            $js.= "$$('#{$id} table')[0].addClassName('system-tooltip-wrap');
                   $$('#{$id} table tbody tr').each(function(tr) {
                       Event.observe(tr, 'mouseover', function (event) {
                           var relatedTarget = $(event.relatedTarget || event.fromElement);
                           if(relatedTarget && (relatedTarget == this || relatedTarget.descendantOf(this))) {
                               return;
                           }
                           showTooltip(event);
                       });
                       Event.observe(tr, 'mouseout', function (event) {
                           var relatedTarget = $(event.relatedTarget || event.toElement);
                           if(relatedTarget && (relatedTarget == this || relatedTarget.childOf(this))) {
                               return;
                           }
                           hideTooltip(event);
                       });
                   });
                   $$('#{$id} table')[0].select('input','select').each(function(field) {
                       Event.observe(field, 'focus', function (event) {
                           showTooltip(event);
                       });
                       Event.observe(field, 'blur', function (event) {
                           hideTooltip(event);
                       });
                   });
                   function showTooltip(event) {
                       var tableHeight = Event.findElement(event, 'table').getStyle('height');
                       var tr = Event.findElement(event, 'tr');
                       var id = tr.id + '_comment';
                       $$('div.system-tooltip-box').invoke('hide');
                       if ($(id)) {
                           $(id).show().setStyle({height : tableHeight});
                           if(document.viewport.getWidth() < 1200) {
                               $(id).addClassName('system-tooltip-small').setStyle({height : 'auto'});
                           } else {
                               $(id).removeClassName('system-tooltip-small');
                           }
                       }
                   };
                   function hideTooltip(event) {
                       var tr = Event.findElement(event, 'tr');
                       var id = tr.id + '_comment';
                       if ($(id)) {
                           setTimeout(function() { $(id).hide(); }, 1);
                       }
                   };";
        }
        return $this->helper('Mage_Core_Helper_Js')->getScript($js);
    }

    /**
     * Collapsed or expanded fieldset when page loaded?
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return bool
     */
    protected function _getCollapseState($element)
    {
        if ($element->getExpanded() !== null) {
            return 1;
        }
        $extra = Mage::getSingleton('Mage_Backend_Model_Auth_Session')->getUser()->getExtra();
        if (isset($extra['configState'][$element->getId()])) {
            return $extra['configState'][$element->getId()];
        }
        return false;
    }
}
