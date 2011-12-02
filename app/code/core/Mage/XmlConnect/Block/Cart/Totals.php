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
 * Shopping cart totals xml renderer
 *
 * @category    Mage
 * @package     Mage_Xmlconnect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_XmlConnect_Block_Cart_Totals extends Mage_Checkout_Block_Cart_Totals
{
    /**
     * Render cart totals xml
     *
     * @return string|Mage_XmlConnect_Model_Simplexml_Element
     */
    protected function _toHtml()
    {
        /** @var $totalsXmlObj Mage_XmlConnect_Model_Simplexml_Element */
        $totalsXmlObj   = Mage::getModel('Mage_XmlConnect_Model_Simplexml_Element', '<totals></totals>');

        foreach ($this->getQuote()->getTotals() as $total) {
            $code  = $total->getCode();
            if ($code == 'giftwrapping') {
                continue;
            }

            $title = '';
            $value = null;
            $renderer = $this->_getTotalRenderer($code)->setTotal($total);

            switch ($code) {
                case 'subtotal':
                    if ($renderer->displayBoth()) {
                        $title = $this->__('Subtotal (Excl. Tax)');
                        $this->_addTotalDataToXmlObj(
                            $totalsXmlObj,
                            $code . '_excl_tax',
                            $title,
                            $total->getValueExclTax()
                        );

                        $code  = $code . '_incl_tax';
                        $title = $this->__('Subtotal (Incl. Tax)');
                        $value = $total->getValueInclTax();
                    }
                    break;
                case 'shipping':
                    if ($renderer->displayBoth()) {
                        $title = $renderer->getExcludeTaxLabel();
                        $this->_addTotalDataToXmlObj(
                            $totalsXmlObj, $code . '_excl_tax', $title, $renderer->getShippingExcludeTax()
                        );

                        $code  = $code . '_incl_tax';
                        $title = $renderer->getIncludeTaxLabel();
                        $value = $renderer->getShippingIncludeTax();
                    } else if ($renderer->displayIncludeTax()) {
                        $value = $renderer->getShippingIncludeTax();
                    } else {
                        $value = $renderer->getShippingExcludeTax();
                    }
                    break;
                case 'grand_total':
                    $grandTotalExlTax = $renderer->getTotalExclTax();
                    $displayBoth = $renderer->includeTax() && $grandTotalExlTax >= 0;
                    if ($displayBoth) {
                        $title = $this->__('Grand Total (Excl. Tax)');
                        $this->_addTotalDataToXmlObj(
                            $totalsXmlObj, $code . '_excl_tax', $title, $grandTotalExlTax
                        );
                        $code  = $code . '_incl_tax';
                        $title = $this->__('Grand Total (Incl. Tax)');
                    }
                    break;
                case 'giftwrapping':
                    foreach ($renderer->getValues() as $title => $value) {
                        $this->_addTotalDataToXmlObj($totalsXmlObj, $code, $title, $value);
                    }
                    continue 2;
                case 'giftcardaccount':
                    $_cards = $renderer->getTotal()->getGiftCards();
                    if (!$_cards) {
                        $_cards = $renderer->getQuoteGiftCards();
                    }
                    if ($renderer->getTotal()->getValue()) {
                        foreach ($_cards as $cardCode) {
                            $title = $this->__('Gift Card (%s)', $cardCode['c']);
                            $value = $cardCode['c'];
                            $totalXmlObj = $totalsXmlObj->addChild($code);
                            $totalXmlObj->addChild('title', $totalsXmlObj->xmlentities($title));
                            $totalXmlObj->addChild('value', $value);
                            $value = Mage::helper('Mage_XmlConnect_Helper_Data')->formatPriceForXml($cardCode['a']);
                            $formattedValue = $this->getQuote()->getStore()->formatPrice($value, false);
                            $totalXmlObj->addChild('formated_value', '-' . $formattedValue);
                        }
                    }
                    continue 2;
                default:
                    break;
            }
            if (empty($title)) {
                $title = $total->getTitle();
            }
            if (null === $value) {
                $value = $total->getValue();
            }
            if (null !== $value) {
                $this->_addTotalDataToXmlObj($totalsXmlObj, $code, $title, $value);
            }
        }

        return $this->getReturnObjectFlag() ? $totalsXmlObj : $totalsXmlObj->asNiceXml();
    }

    /**
     * Add total data to totals xml object
     *
     * @param Mage_XmlConnect_Model_Simplexml_Element $totalsXmlObj
     * @param string $code
     * @param string $title
     * @param float $value
     */
    protected function _addTotalDataToXmlObj($totalsXmlObj, $code, $title, $value)
    {
        $value = Mage::helper('Mage_XmlConnect_Helper_Data')->formatPriceForXml($value);
        $totalXmlObj = $totalsXmlObj->addChild($code);
        $totalXmlObj->addChild('title', $totalsXmlObj->xmlentities($title));
        $formattedValue = $this->getQuote()->getStore()->formatPrice($value, false);
        $totalXmlObj->addChild('value', $value);
        $totalXmlObj->addChild('formated_value', $formattedValue);
    }
}
