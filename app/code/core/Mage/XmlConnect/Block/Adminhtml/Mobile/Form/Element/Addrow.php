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
 * @package     Mage_XmlConnect
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Xmlconnect Add row form element
 *
 * @category    Mage
 * @package     Mage_XmlConnect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_XmlConnect_Block_Adminhtml_Mobile_Form_Element_Addrow
    extends Varien_Data_Form_Element_Button
{
    /**
     * Render Element Html
     *
     * @return string
     */
    public function getElementHtml()
    {
        $html = $this->getBeforeElementHtml()
            . '<button id="'.$this->getHtmlId()
            . '" name="'
            . $this->getName()
            . '" value="'.$this->getEscapedValue()
            . '" '
            . $this->serialize($this->getHtmlAttributes())
            . ' ><span><span><span>'
            . $this->getEscapedValue()
            . '</span></span></span></button>'
            . $this->getAfterElementHtml();
        return $html;
    }

    /**
     * Getter for "before_element_html"
     *
     * @return string
     */
    public function getBeforeElementHtml()
    {
        return $this->getData('before_element_html');
    }

    /**
     * Return label html code
     *
     * @param string $idSuffix
     * @return string
     */
    public function getLabelHtml($idSuffix = '')
    {
        if ($this->getLabel() !== null) {
            $html = '<label  for="' . $this->getHtmlId() . $idSuffix . '">'
                . $this->getLabel()
                . ($this->getRequired() ? ' <span class="required">*</span>' : '')
                . '</label>';
        } else {
            $html = '';
        }
        return $html;
    }

    /**
     * Overriding toHtml parent method
     * Adding addrow Block to element renderer
     *
     * @return string
     */
    public function toHtml()
    {
        $blockClassName = Mage::getConfig()->getBlockClassName('Mage_Adminhtml_Block_Template');
        //TODO: Get rid from Mage::app
        $jsBlock = Mage::app()->getLayout()->createBlock($blockClassName);
        $jsBlock->setTemplate('Mage_XmlConnect::form/element/addrow.phtml');
        $jsBlock->setOptions($this->getOptions());
        return parent::toHtml() . $jsBlock->toHtml();
    }
}
