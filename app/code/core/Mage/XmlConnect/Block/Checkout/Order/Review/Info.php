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
 * One page checkout order info xml renderer
 *
 * @category    Mage
 * @package     Mage_XmlConnect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_XmlConnect_Block_Checkout_Order_Review_Info extends Mage_Checkout_Block_Onepage_Review_Info
{
    /**
     * Render order review items
     *
     * @return string
     */
    protected function _toHtml()
    {
        $itemsXmlObj = Mage::getModel('Mage_XmlConnect_Model_Simplexml_Element',
            array('data' => '<products></products>'));
        $quote = Mage::getSingleton('Mage_Checkout_Model_Session')->getQuote();

        $helper = Mage::helper('Mage_XmlConnect_Helper_Data');
        $taxHelper = $this->helper('Mage_Tax_Helper_Data');
        $weeeHelper = Mage::helper('Mage_Weee_Helper_Data');
        $checkoutHelper = $this->helper('Mage_Checkout_Helper_Data');

        /* @var $item Mage_Sales_Model_Quote_Item */
        foreach ($this->getItems() as $item) {
            $type = $this->_getItemType($item);
            $renderer = $this->getItemRenderer($type)->setItem($item);

            /**
             * General information
             */
            $itemXml = $itemsXmlObj->addChild('item');
            $itemXml->addChild('entity_id', $item->getProduct()->getId());
            $itemXml->addChild('entity_type', $type);
            $itemXml->addChild('item_id', $item->getId());
            $itemXml->addChild('name', $itemsXmlObj->escapeXml($renderer->getProductName()));
            $itemXml->addChild('qty', $renderer->getQty());
            $icon = $renderer->getProductThumbnail()->resize(
                Mage::helper('Mage_XmlConnect_Helper_Image')->getImageSizeForContent('product_small')
            );

            $iconXml = $itemXml->addChild('icon', $icon);

            $file = $helper->urlToPath($icon);
            $iconXml->addAttribute('modification_time', filemtime($file));

            /**
             * Price
             */
            $exclPrice = $inclPrice = 0.00;
            if ($taxHelper->displayCartPriceExclTax() || $taxHelper->displayCartBothPrices()) {
                $typeOfDisplay = $weeeHelper->typeOfDisplay($item, array(0, 1, 4), 'sales');
                if ($typeOfDisplay && $item->getWeeeTaxAppliedAmount()) {
                    $exclPrice = $item->getCalculationPrice() + $item->getWeeeTaxAppliedAmount()
                        + $item->getWeeeTaxDisposition();
                } else {
                    $exclPrice = $item->getCalculationPrice();
                }
            }

            if ($taxHelper->displayCartPriceInclTax() || $taxHelper->displayCartBothPrices()) {
                $_incl = $checkoutHelper->getPriceInclTax($item);
                $typeOfDisplay = $weeeHelper->typeOfDisplay($item, array(0, 1, 4), 'sales');
                if ($typeOfDisplay && $item->getWeeeTaxAppliedAmount()) {
                    $inclPrice = $_incl + $item->getWeeeTaxAppliedAmount();
                } else {
                    $inclPrice = $_incl - $item->getWeeeTaxDisposition();
                }
            }

            $exclPrice = $helper->formatPriceForXml($exclPrice);
            $formattedExclPrice = $quote->getStore()->formatPrice($exclPrice, false);

            $inclPrice = $helper->formatPriceForXml($inclPrice);
            $formattedInclPrice = $quote->getStore()->formatPrice($inclPrice, false);

            $priceXmlObj = $itemXml->addChild('price');
            $formattedPriceXmlObj = $itemXml->addChild('formated_price');

            if ($taxHelper->displayCartBothPrices()) {
                $priceXmlObj->addAttribute('excluding_tax', $exclPrice);
                $priceXmlObj->addAttribute('including_tax', $inclPrice);

                $formattedPriceXmlObj->addAttribute('excluding_tax', $formattedExclPrice);
                $formattedPriceXmlObj->addAttribute('including_tax', $formattedInclPrice);
            } else {
                if ($taxHelper->displayCartPriceExclTax()) {
                    $priceXmlObj->addAttribute('regular', $exclPrice);
                    $formattedPriceXmlObj->addAttribute('regular', $formattedExclPrice);
                }
                if ($taxHelper->displayCartPriceInclTax()) {
                    $priceXmlObj->addAttribute('regular', $inclPrice);
                    $formattedPriceXmlObj->addAttribute('regular', $formattedInclPrice);
                }
            }

            /**
             * Subtotal
             */
            $exclPrice = $inclPrice = 0.00;
            if ($taxHelper->displayCartPriceExclTax() || $taxHelper->displayCartBothPrices()) {
                $typeOfDisplay = $weeeHelper->typeOfDisplay($item, array(0, 1, 4), 'sales');
                if ($typeOfDisplay && $item->getWeeeTaxAppliedAmount()) {
                    $exclPrice = $item->getRowTotal() + $item->getWeeeTaxAppliedRowAmount()
                        + $item->getWeeeTaxRowDisposition();
                } else {
                    $exclPrice = $item->getRowTotal();
                }
            }
            if ($taxHelper->displayCartPriceInclTax() || $taxHelper->displayCartBothPrices()) {
                $_incl = $checkoutHelper->getSubtotalInclTax($item);
                if ($weeeHelper->typeOfDisplay($item, array(0, 1, 4), 'sales')
                    && $item->getWeeeTaxAppliedAmount()
                ) {
                    $inclPrice = $_incl + $item->getWeeeTaxAppliedRowAmount();
                } else {
                    $inclPrice = $_incl - $item->getWeeeTaxRowDisposition();
                }
            }

            $exclPrice = $helper->formatPriceForXml($exclPrice);
            $formattedExclPrice = $quote->getStore()->formatPrice($exclPrice, false);

            $inclPrice = $helper->formatPriceForXml($inclPrice);
            $formattedInclPrice = $quote->getStore()->formatPrice($inclPrice, false);

            $subtotalPriceXmlObj = $itemXml->addChild('subtotal');
            $subtotalFormattedPriceXmlObj = $itemXml->addChild('formated_subtotal');

            if ($taxHelper->displayCartBothPrices()) {
                $subtotalPriceXmlObj->addAttribute('excluding_tax', $exclPrice);
                $subtotalPriceXmlObj->addAttribute('including_tax', $inclPrice);

                $subtotalFormattedPriceXmlObj->addAttribute('excluding_tax', $formattedExclPrice);
                $subtotalFormattedPriceXmlObj->addAttribute('including_tax', $formattedInclPrice);
            } else {
                if ($taxHelper->displayCartPriceExclTax()) {
                    $subtotalPriceXmlObj->addAttribute('regular', $exclPrice);
                    $subtotalFormattedPriceXmlObj->addAttribute('regular', $formattedExclPrice);
                }
                if ($taxHelper->displayCartPriceInclTax()) {
                    $subtotalPriceXmlObj->addAttribute('regular', $inclPrice);
                    $subtotalFormattedPriceXmlObj->addAttribute('regular', $formattedInclPrice);
                }
            }

            /**
             * Options list
             */
            $_options = $renderer->getOptionList();
            if ($_options) {
                $itemOptionsXml = $itemXml->addChild('options');
                foreach ($_options as $_option) {
                    $_formattedOptionValue = $renderer->getFormatedOptionValue($_option);
                    $optionXml = $itemOptionsXml->addChild('option');
                    $labelValue = $itemsXmlObj->escapeXml($_option['label']);
                    $optionXml->addAttribute('label', $labelValue);
                    $textValue = $itemsXmlObj->escapeXml($_formattedOptionValue['value']);
                    $optionXml->addAttribute('text', $textValue);
                }
            }
        }

        return $itemsXmlObj->asNiceXml();
    }
}
