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
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Fieldset config form element renderer
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Catalog_Product_Frontend_Product_Watermark
    extends Mage_Core_Block_Template
    implements Varien_Data_Form_Element_Renderer_Interface
{
    const XML_PATH_IMAGE_TYPES = 'global/catalog/product/media/image_types';

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $html = $this->_getHeaderHtml($element);
        $renderer = Mage::getBlockSingleton('Mage_Backend_Block_System_Config_Form_Field');

        $attributes = Mage::getConfig()->getNode(self::XML_PATH_IMAGE_TYPES)->asArray();

        foreach ($attributes as $key => $attribute) {
            /**
             * Watermark size field
             */
            $field = new Varien_Data_Form_Element_Text();
            $field->setName("groups[watermark][fields][{$key}_size][value]")
                ->setForm( $this->getForm() )
                ->setLabel(Mage::helper('Mage_Adminhtml_Helper_Data')->__('Size for %s', $attribute['title']))
                ->setRenderer($renderer);
            $html.= $field->toHtml();

            /**
             * Watermark upload field
             */
            $field = new Varien_Data_Form_Element_Imagefile();
            $field->setName("groups[watermark][fields][{$key}_image][value]")
                ->setForm( $this->getForm() )
                ->setLabel(Mage::helper('Mage_Adminhtml_Helper_Data')->__('Watermark File for %s', $attribute['title']))
                ->setRenderer($renderer);
            $html.= $field->toHtml();

            /**
             * Watermark position field
             */
            $field = new Varien_Data_Form_Element_Select();
            $field->setName("groups[watermark][fields][{$key}_position][value]")
                ->setForm( $this->getForm() )
                ->setLabel(Mage::helper('Mage_Adminhtml_Helper_Data')->__('Position of Watermark for %s', $attribute['title']))
                ->setRenderer($renderer)
                ->setValues(Mage::getSingleton('Mage_Catalog_Model_Config_Source_Watermark_Position')->toOptionArray());
            $html.= $field->toHtml();
        }

        $html .= $this->_getFooterHtml($element);

        return $html;
    }

    protected function _getHeaderHtml($element)
    {
        $id = $element->getHtmlId();
        $default = !$this->getRequest()->getParam('website') && !$this->getRequest()->getParam('store');

        $html = '<h4 class="icon-head head-edit-form">'.$element->getLegend().'</h4>';
        $html.= '<fieldset class="config" id="'.$element->getHtmlId().'">';
        $html.= '<legend>'.$element->getLegend().'</legend>';

        // field label column
        $html.= '<table cellspacing="0"><colgroup class="label" /><colgroup class="value" />';
        if (!$default) {
            $html.= '<colgroup class="use-default" />';
        }
        $html.= '<tbody>';

        return $html;
    }

    protected function _getFooterHtml($element)
    {
        $html = '</tbody></table></fieldset>';
        return $html;
    }
}
