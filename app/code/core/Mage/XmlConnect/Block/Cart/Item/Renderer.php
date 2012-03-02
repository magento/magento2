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
 * Shopping cart default item xml renderer
 *
 * @category    Mage
 * @package     Mage_XmlConnect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_XmlConnect_Block_Cart_Item_Renderer extends Mage_Checkout_Block_Cart_Item_Renderer
{
    /**
     * Add product details to XML object
     *
     * @param Mage_XmlConnect_Model_Simplexml_Element $reviewXmlObj
     * @return Mage_XmlConnect_Model_Simplexml_Element
     */
    public function addProductToXmlObj(Mage_XmlConnect_Model_Simplexml_Element $reviewXmlObj)
    {
        $_item = $this->getItem();
        $productXmlObj = $reviewXmlObj->addCustomChild('item');
        $productXmlObj->addCustomChild('name', $this->escapeHtml($this->getProductName()));

        if ($_options = $this->getOptionList()) {
            $optionsXmlObj = $productXmlObj->addChild('options');
            foreach ($_options as $_option) {
                $_formattedOptionValue = $this->getFormatedOptionValue($_option);

                if (isset($_formattedOptionValue['full_view'])) {
                    $value = $_formattedOptionValue['full_view'];
                } else {
                    $value = null;
                }

                $optionsXmlObj->addCustomChild('option', $value, array(
                    'label' => $this->escapeHtml($_option['label']),
                    'value' => $_formattedOptionValue['value']
                ));
            }
        }

        $this->_addPriceToXmlObj($productXmlObj);
        $this->_addSubtotalToXmlObj($productXmlObj);

        $productXmlObj->addCustomChild('qty', $_item->getQty());

        return $reviewXmlObj;
    }

    /**
     * Add product subtotal info to xml object
     *
     * @param Mage_XmlConnect_Model_Simplexml_Element $productXmlObj
     * @return Mage_XmlConnect_Model_Simplexml_Element
     */
    protected function _addSubtotalToXmlObj(Mage_XmlConnect_Model_Simplexml_Element $productXmlObj)
    {
        $_item = $this->getItem();
        $subtotalXmlObj = $productXmlObj->addCustomChild('subtotal');

        $taxHelper = $this->helper('Mage_Tax_Helper_Data');
        $weeeHelper = Mage::helper('Mage_Weee_Helper_Data');
        if ($taxHelper->displayCartPriceExclTax() || $taxHelper->displayCartBothPrices()) {
            if ($weeeHelper->typeOfDisplay($_item, array(0, 1, 4), 'sales')
                && $_item->getWeeeTaxAppliedAmount()
            ) {
                $exclPrice = $_item->getRowTotal() + $_item->getWeeeTaxAppliedRowAmount()
                    + $_item->getWeeeTaxRowDisposition();
            } else {
                $exclPrice = $_item->getRowTotal();
            }
            $exclPrice = $this->_formatPrice($exclPrice);
            $subtotalXmlObj->addAttribute('excluding_tax', $subtotalXmlObj->escapeXml($exclPrice));
        }

        if ($taxHelper->displayCartPriceInclTax() || $taxHelper->displayCartBothPrices()) {
            $_incl = $this->helper('Mage_Checkout_Helper_Data')->getSubtotalInclTax($_item);

            if ($weeeHelper->typeOfDisplay($_item, array(0, 1, 4), 'sales')
                && $_item->getWeeeTaxAppliedAmount()
            ) {
                $inclPrice = $_incl + $_item->getWeeeTaxAppliedRowAmount();
            } else {
                $inclPrice = $_incl - $_item->getWeeeTaxRowDisposition();
            }
            $inclPrice = $this->_formatPrice($inclPrice);

            $subtotalXmlObj->addAttribute('including_tax', $subtotalXmlObj->escapeXml($inclPrice));
        }

        if ($weeeHelper->getApplied($_item)) {
            $this->_addWeeeToXmlObj($subtotalXmlObj, true);
        }

        return $productXmlObj;
    }

    /**
     * Format product price
     *
     * @param int $price
     * @return float
     */
    protected function _formatPrice($price)
    {
        return $this->getQuote()->getStore()->formatPrice($price, false);
    }

    /**
     * Add product price info to xml object
     *
     * @param Mage_XmlConnect_Model_Simplexml_Element $productXmlObj
     * @return Mage_XmlConnect_Model_Simplexml_Element
     */
    protected function _addPriceToXmlObj(Mage_XmlConnect_Model_Simplexml_Element $productXmlObj)
    {
        $_item = $this->getItem();
        $priceXmlObj = $productXmlObj->addCustomChild('price');

        if ($this->helper('Mage_Tax_Helper_Data')->displayCartPriceExclTax()
            || $this->helper('Mage_Tax_Helper_Data')->displayCartBothPrices()
        ) {
            if (Mage::helper('Mage_Weee_Helper_Data')->typeOfDisplay($_item, array(0, 1, 4), 'sales')
                && $_item->getWeeeTaxAppliedAmount()
            ) {
                $exclPrice = $_item->getCalculationPrice() + $_item->getWeeeTaxAppliedAmount()
                    + $_item->getWeeeTaxDisposition();
            } else {
                $exclPrice = $_item->getCalculationPrice();
            }
            $exclPrice = $this->_formatPrice($exclPrice);

            $priceXmlObj->addAttribute('excluding_tax', $priceXmlObj->escapeXml($exclPrice));
        }

        if ($this->helper('Mage_Tax_Helper_Data')->displayCartPriceInclTax()
            || $this->helper('Mage_Tax_Helper_Data')->displayCartBothPrices()
        ) {
            $_incl = $this->helper('Mage_Checkout_Helper_Data')->getPriceInclTax($_item);

            if (Mage::helper('Mage_Weee_Helper_Data')->typeOfDisplay($_item, array(0, 1, 4), 'sales')
                && $_item->getWeeeTaxAppliedAmount()
            ) {
                $inclPrice = $_incl + $_item->getWeeeTaxAppliedAmount();
            } else {
                $inclPrice = $_incl - $_item->getWeeeTaxDisposition();
            }
            $inclPrice = $this->_formatPrice($inclPrice);

            $priceXmlObj->addAttribute('including_tax', $priceXmlObj->escapeXml($inclPrice));
        }

        if (Mage::helper('Mage_Weee_Helper_Data')->getApplied($_item)) {
            $this->_addWeeeToXmlObj($priceXmlObj);
        }

        return $productXmlObj;
    }

    /**
     * Add weee tax product info to xml object
     *
     * @param Mage_XmlConnect_Model_Simplexml_Element $priceXmlObj
     * @param bool $subtotalFlag use true to get subtotal product info
     * @return Mage_XmlConnect_Model_Simplexml_Element
     */
    protected function _addWeeeToXmlObj(Mage_XmlConnect_Model_Simplexml_Element $priceXmlObj, $subtotalFlag = false)
    {
        $_item = $this->getItem();
        $weeeXmlObj = $priceXmlObj->addCustomChild('weee');

        $checkoutHelper = $this->helper('Mage_Checkout_Helper_Data');
        if ($subtotalFlag) {
            $_incl = $checkoutHelper->getSubtotalInclTax($_item);
        } else {
            $_incl = $checkoutHelper->getPriceInclTax($_item);
        }

        $weeeHelper = Mage::helper('Mage_Weee_Helper_Data');
        $typeOfDisplay2 = $weeeHelper->typeOfDisplay($_item, 2, 'sales');

        if ($weeeHelper->typeOfDisplay($_item, 1, 'sales') && $_item->getWeeeTaxAppliedAmount()) {
            foreach ($weeeHelper->getApplied($_item) as $tax) {

                if ($subtotalFlag) {
                    $amount = $tax['row_amount'];
                } else {
                    $amount = $tax['amount'];
                }

                $weeeXmlObj->addCustomChild('item', null, array(
                    'name'      => $tax['title'],
                    'amount'    => $this->_formatPrice($amount)
                ));
            }
        } elseif ($_item->getWeeeTaxAppliedAmount()
            && ($typeOfDisplay2 || $weeeHelper->typeOfDisplay($_item, 4, 'sales'))
        ) {
            foreach ($weeeHelper->getApplied($_item) as $tax) {
                if ($subtotalFlag) {
                    $amount = $tax['row_amount_incl_tax'];
                } else {
                    $amount = $tax['amount_incl_tax'];
                }

                $weeeXmlObj->addCustomChild('item', null, array(
                    'name'      => $tax['title'],
                    'amount'    => $this->_formatPrice($amount)
                ));
            }
        }

        if ($typeOfDisplay2 && $_item->getWeeeTaxAppliedAmount()) {
            if ($subtotalFlag) {
                $totalExcl = $_item->getRowTotal() + $_item->getWeeeTaxAppliedRowAmount()
                    + $_item->getWeeeTaxRowDisposition();
            } else {
                $totalExcl = $_item->getCalculationPrice() + $_item->getWeeeTaxAppliedAmount()
                    + $_item->getWeeeTaxDisposition();
            }

            $totalExcl = $this->_formatPrice($totalExcl);
            $priceXmlObj->addAttribute('total_excluding_tax', $priceXmlObj->escapeXml($totalExcl));
        }

        if ($typeOfDisplay2 && $_item->getWeeeTaxAppliedAmount()) {
            if ($subtotalFlag) {
                $totalIncl = $_incl + $_item->getWeeeTaxAppliedRowAmount();
            } else {
                $totalIncl = $_incl + $_item->getWeeeTaxAppliedAmount();
            }

            $totalIncl = $this->_formatPrice($totalIncl);
            $priceXmlObj->addAttribute('total_including_tax', $priceXmlObj->escapeXml($totalIncl));
        }

        return $priceXmlObj;
    }
}
