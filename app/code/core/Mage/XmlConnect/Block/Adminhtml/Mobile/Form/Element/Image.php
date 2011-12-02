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
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * XmlConnect image form element
 *
 * @category    Mage
 * @package     Mage_XmlConnect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_XmlConnect_Block_Adminhtml_Mobile_Form_Element_Image
    extends Varien_Data_Form_Element_Image
{
    /**
     * Function fetches image Url actual or default
     *
     * @return string
     */
    protected function _getUrl()
    {
        if ($this->getValue()) {
            if (strpos($this->getValue(), '://') === false) {
                $url = Mage::helper('Mage_XmlConnect_Helper_Image')->getFileDefaultSizeSuffixAsUrl($this->getValue());
                $url = Mage::helper('Mage_XmlConnect_Helper_Image')->getMediaUrl($url);
            } else {
                $url = $this->getValue();
            }
        } else {
            $url = $this->getDefaultValue();
        }
        return $url;
    }

    /**
     * Get "clear" filename from element
     *
     * @return string
     */
    public function getUploadName()
    {
        /**
         * Ugly hack to avoid $_FILES[..]['name'][..][..]
         */
        $name = $this->getName();
        $name = strtr($name, array('[' => '/', ']' => ''));
        return $name;
    }

    /**
     * Compose output html for element
     *
     * @return string
     */
    public function getElementHtml()
    {
        $html = '<div style="white-space: nowrap">';

        $url = $this->_getUrl();
        $html .= '<a href="' . $url . '" onclick="imagePreview(\'' . $this->getHtmlId() . '_image\'); return false;">';
        $html .= '<img src="' . $url . '" id="' . $this->getHtmlId() . '_image"';
        $html .= ' alt="" height="22" width="22" class="small-image-preview v-middle" /></a> ';

        $html .= '<input id="' . $this->getHtmlId() . '_hidden" name="' . $this->getName();
        $html .= '" value="' . $this->getEscapedValue() . '" type="hidden" />';

        $this->setClass('input-file');
        $html .= '<input id="' . $this->getHtmlId() . '" name="' . $this->getUploadName();
        $attr = $this->serialize($this->getHtmlAttributes());
        $html .= '" value="' . $this->getEscapedValue() . '" ' . $attr . '/>' . PHP_EOL;
        $html .= $this->getAfterElementHtml();

        $html .= '</div>';

        return $html;
    }
}
