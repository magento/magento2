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
 * Customer order details helper
 *
 * @category    Mage
 * @package     Mage_XmlConnect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_XmlConnect_Helper_Customer_Order extends Mage_Core_Helper_Abstract
{
    /**#@+
     * Price display type
     * @see Mage_Weee_Helper_Data::typeOfDisplay(...);
     */
    const PRICE_DISPLAY_TYPE_1 = 1;
    const PRICE_DISPLAY_TYPE_2 = 2;
    const PRICE_DISPLAY_TYPE_4 = 4;
    const PRICE_DISPLAY_TYPE_14 = 14;
    /**#@-*/

    /**
     * Add Weee taxes child to the XML
     *
     * @param Mage_Core_Block_Template $renderer Product renderer
     * @param Mage_Sales_Model_Order_Item $item
     * @param Mage_XmlConnect_Model_Simplexml_Element $priceXml
     * @param Mage_XmlConnect_Model_Simplexml_Element $subtotalXml
     * @param bool $isIncludeTax
     * @return null
     */
    public function addPriceAndSubtotalToXml(Mage_Core_Block_Template $renderer, Mage_Sales_Model_Order_Item $item,
        Mage_XmlConnect_Model_Simplexml_Element $priceXml, Mage_XmlConnect_Model_Simplexml_Element $subtotalXml,
        $isIncludeTax = false
    ) {
        $weeeParams = array();

        $typesOfDisplay = $renderer->getTypesOfDisplay();
        if ($isIncludeTax) {
            $nodeName = 'including_tax';
            $nodeLabel = Mage::helper('Mage_Tax_Helper_Data')->__('Incl. Tax');

            $inclPrice      = $renderer->helper('Mage_Checkout_Helper_Data')->getPriceInclTax($item);
            $inclSubtotal   = $renderer->helper('Mage_Checkout_Helper_Data')->getSubtotalInclTax($item);

            if ($typesOfDisplay[self::PRICE_DISPLAY_TYPE_14]) {
                $price      = $inclPrice + $renderer->getWeeeTaxAppliedAmount();
                $subtotal   = $inclSubtotal + $item->getWeeeTaxAppliedRowAmount();
            } else {
                $price      = $inclPrice - $renderer->getWeeeTaxDisposition();
                $subtotal   = $inclSubtotal - $item->getWeeeTaxRowDisposition();
            }
            $weeeParams['include'] = $inclPrice;
        } else {
            $nodeName = 'excluding_tax';
            $nodeLabel = Mage::helper('Mage_Tax_Helper_Data')->__('Excl. Tax');

            if ($typesOfDisplay[self::PRICE_DISPLAY_TYPE_14]) {
                $price = $item->getPrice() + $renderer->getWeeeTaxAppliedAmount()
                    + $renderer->getWeeeTaxDisposition();
                $subtotal = $item->getRowTotal() + $item->getWeeeTaxAppliedRowAmount()
                    + $item->getWeeeTaxRowDisposition();
            } else {
                $price = $item->getPrice();
                $subtotal = $item->getRowTotal();
            }
        }

        $configNode = array(
            'value' => $this->formatPrice($renderer, $price)
        );
        if ($renderer->helper('Mage_Tax_Helper_Data')->displaySalesBothPrices()) {
            $configNode['label'] = $nodeLabel;
        }

        $this->addWeeeTaxesToPriceXml(
            $renderer, $item, $priceXml->addCustomChild($nodeName, null, $configNode), $weeeParams
        );

        $configNode['value']        = $this->formatPrice($renderer, $subtotal);
        $weeeParams['include']      = $isIncludeTax ? $inclSubtotal : null;
        $weeeParams['is_subtotal']  = true;
        $this->addWeeeTaxesToPriceXml(
            $renderer, $item, $subtotalXml->addCustomChild($nodeName, null, $configNode), $weeeParams
        );
    }

    /**
     * Add Product options to XML
     *
     * @param Mage_Core_Block_Template $renderer Product renderer
     * @param Mage_XmlConnect_Model_Simplexml_Element $itemXml
     * @return null
     */
    public function addItemOptionsToXml(Mage_Core_Block_Template $renderer,
        Mage_XmlConnect_Model_Simplexml_Element $itemXml
    ) {
        $options = $renderer->getItemOptions();
        if (!empty($options)) {
            $optionsXml = $itemXml->addChild('options');

            foreach ($options as $option) {
                $value = false;
                $formatedOptionValue = $renderer->getFormatedOptionValue($option);
                if (isset($formatedOptionValue['full_view']) && isset($formatedOptionValue['value'])) {
                    $value = $formatedOptionValue['value'];
                } elseif (isset($option['print_value'])) {
                    $value = $option['print_value'];
                } elseif (isset($option['value'])) {
                    $value = $option['value'];
                }
                if ($value) {
                    $optionsXml->addCustomChild('option', strip_tags($value), array('label' => $option['label']));
                }
            }
        }
    }

    /**
     * Add Weee taxes child to the XML
     *
     * @param Mage_Core_Block_Template $renderer Product renderer
     * @param Mage_Sales_Model_Order_Item $item
     * @param Mage_XmlConnect_Model_Simplexml_Element $parentXml
     * @param array $params Params for Weee taxes: 'include' - Price including tax, 'is_subtotal' - Flag of subtotal
     * @return null
     */
    public function addWeeeTaxesToPriceXml(Mage_Core_Block_Template $renderer, Mage_Sales_Model_Order_Item $item,
        Mage_XmlConnect_Model_Simplexml_Element $parentXml, $params = array()
    ) {
        $weeTaxes = $renderer->getWeeeTaxes();
        if (empty($weeTaxes)) {
            return;
        }

        $typesOfDisplay = $renderer->getTypesOfDisplay();

        $row = isset($params['is_subtotal']) && $params['is_subtotal'] ? 'row_' : '';

        /** @var $weeeXml Mage_XmlConnect_Model_Simplexml_Element */
        if ($typesOfDisplay[self::PRICE_DISPLAY_TYPE_1]) {
            $weeeXml = $parentXml->addChild('weee');
            foreach ($weeTaxes as $tax) {
                $weeeXml->addCustomChild('tax', $this->formatPrice($renderer, $tax[$row . 'amount']),
                    array('label' => $tax['title'])
                );
            }
        } elseif ($typesOfDisplay[self::PRICE_DISPLAY_TYPE_2] || $typesOfDisplay[self::PRICE_DISPLAY_TYPE_4]) {
            $weeeXml = $parentXml->addChild('weee');
            foreach ($weeTaxes as $tax) {
                $weeeXml->addCustomChild('tax', $this->formatPrice($renderer, $tax[$row . 'amount_incl_tax']),
                    array('label' => $tax['title'])
                );
            }
        }

        if ($typesOfDisplay[self::PRICE_DISPLAY_TYPE_2]) {
            if (!is_null($params['include'])) {
                // including tax
                if (isset($params['is_subtotal'])) {
                    $total = $params['include'] + $item->getWeeeTaxAppliedRowAmount();
                } else {
                    $total = $params['include'] + $renderer->getWeeeTaxAppliedAmount();
                }
            } else {
                // excluding tax
                if ($params['is_subtotal']) {
                    $total = $item->getRowTotal() + $item->getWeeeTaxAppliedRowAmount()
                        + $item->getWeeeTaxRowDisposition();
                } else {
                    $total = $item->getPrice() + $renderer->getWeeeTaxAppliedAmount()
                        + $renderer->getWeeeTaxDisposition();
                }
            }

            if (!isset($weeeXml)) {
                $weeeXml = $parentXml->addChild('weee');
            }
            $weeeXml->addCustomChild(
                'total',
                $this->formatPrice($renderer, $total),
                array('label' => Mage::helper('Mage_Weee_Helper_Data')->__('Total'))
            );
        }
    }

    /**
     * Add item quantities to the XML
     *
     * @param Mage_Core_Block_Template $renderer Product renderer
     * @param Mage_XmlConnect_Model_Simplexml_Element $quantityXml
     * @param Mage_Sales_Model_Order_Item $item
     * @return null
     */
    public function addQuantityToXml(Mage_Core_Block_Template $renderer,
        Mage_XmlConnect_Model_Simplexml_Element $quantityXml, Mage_Sales_Model_Order_Item $item
    ) {
        $qty = 1 * $item->getQtyOrdered();
        if ($qty > 0) {
            $quantityXml->addCustomChild('value', $qty, array('label' => Mage::helper('Mage_XmlConnect_Helper_Data')->__('Ordered')));
        }
        $qty = 1 * $item->getQtyShipped();
        if ($qty > 0) {
            $quantityXml->addCustomChild('value', $qty, array('label' => Mage::helper('Mage_XmlConnect_Helper_Data')->__('Shipped')));
        }
        $qty = 1 * $item->getQtyCanceled();
        if ($qty > 0) {
            $quantityXml->addCustomChild('value', $qty, array('label' => Mage::helper('Mage_XmlConnect_Helper_Data')->__('Canceled')));
        }
        $qty = 1 * $item->getQtyRefunded();
        if ($qty > 0) {
            $quantityXml->addCustomChild('value', $qty, array('label' => Mage::helper('Mage_XmlConnect_Helper_Data')->__('Refunded')));
        }
    }

    /**
     * Format price using order currency
     *
     * @param Mage_Core_Block_Template $renderer Product renderer
     * @param float $price
     * @return string
     */
    public function formatPrice(Mage_Core_Block_Template $renderer, $price)
    {
        return $renderer->getOrder()->getOrderCurrency()->formatPrecision($price, 2, array(), false);
    }
}
