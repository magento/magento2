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
 * Bundle product price xml renderer
 *
 * @category    Mage
 * @package     Mage_XmlConnect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_XmlConnect_Block_Catalog_Product_Price_Bundle extends Mage_Bundle_Block_Catalog_Product_Price
{
    /**
     * Collect product prices to specified item xml object
     *
     * @param Mage_Catalog_Model_Product $product
     * @param Mage_XmlConnect_Model_Simplexml_Element $item
     */
    public function collectProductPrices(
        Mage_Catalog_Model_Product $product, Mage_XmlConnect_Model_Simplexml_Element $item
    ) {
        $this->setProduct($product)->setDisplayMinimalPrice(true) ->setUseLinkForAsLowAs(false);

        $priceXmlObj = $item->addChild('price');

        /** @var $_coreHelper Mage_Core_Helper_Data */
        $_coreHelper = $this->helper('Mage_Core_Helper_Data');
        /** @var $_weeeHelper Mage_Weee_Helper_Data */
        $_weeeHelper = $this->helper('Mage_Weee_Helper_Data');
        /** @var $_taxHelper Mage_Tax_Helper_Data */
        $_taxHelper  = $this->helper('Mage_Tax_Helper_Data');

        $_tierPrices = $this->_getTierPrices($product);

        if (count($_tierPrices) > 0) {
            $tierPricesTextArray = array();
            foreach ($_tierPrices as $_price) {
                $discount = ' ' . ($_price['price'] * 1) . '%';
                $tierPricesTextArray[] = $this->__('Buy %1$s with %2$s discount each', $_price['price_qty'], $discount);
            }
            $item->addChild('price_tier', implode(PHP_EOL, $tierPricesTextArray));
        }

        list($_minimalPrice, $_maximalPrice) = $product->getPriceModel()->getTotalPrices($product);

        $_weeeTaxAmount = 0;
        $_minimalPriceTax = $_taxHelper->getPrice($product, $_minimalPrice);
        $_minimalPriceInclTax = $_taxHelper->getPrice($product, $_minimalPrice, true);

        if ($product->getPriceType() == 1) {
            $_weeeTaxAmount = $_weeeHelper->getAmount($product);
            if ($_weeeTaxAmount && $_weeeHelper->typeOfDisplay($product, array(0, 1, 4))) {
                $_minimalPriceTax += $_weeeTaxAmount;
                $_minimalPriceInclTax += $_weeeTaxAmount;
            }
            if ($_weeeTaxAmount && $_weeeHelper->typeOfDisplay($product, 2)) {
                $_minimalPriceInclTax += $_weeeTaxAmount;
            }

            if ($_weeeHelper->typeOfDisplay($product, array(1, 2, 4))) {
                $_weeeTaxAttributes = $_weeeHelper->getProductWeeeAttributesForDisplay($product);
            }
        }

        if ($product->getPriceView()) {
            if ($_taxHelper->displayBothPrices()) {
                $priceXmlObj->addAttribute(
                    'as_low_as_excluding_tax', $_coreHelper->currency($_minimalPriceTax, true, false)
                );
                if ($_weeeTaxAmount && $product->getPriceType() == 1
                    && $_weeeHelper->typeOfDisplay($product, array(2, 1, 4))
                ) {
                    $weeeXmlObj = $priceXmlObj->addChild('weee');
                    $_weeeSeparator = ' + ';
                    $weeeXmlObj->addAttribute('separator', $_weeeSeparator);
                    foreach ($_weeeTaxAttributes as $_weeeTaxAttribute) {
                        if ($_weeeHelper->typeOfDisplay($product, array(2, 4))) {
                            $amount = $_weeeTaxAttribute->getAmount() + $_weeeTaxAttribute->getTaxAmount();
                        } else {
                            $amount = $_weeeTaxAttribute->getAmount();
                        }
                        $weeeItemXmlObj = $weeeXmlObj->addChild('item');
                        $weeeItemXmlObj->addAttribute(
                            'name', $weeeItemXmlObj->xmlentities($_weeeTaxAttribute->getName())
                        );
                        $weeeItemXmlObj->addAttribute(
                            'amount', $_coreHelper->currency($amount, true, false)
                        );
                    }
                }
                $priceXmlObj->addAttribute(
                    'as_low_as_including_tax', $_coreHelper->currency($_minimalPriceInclTax, true, false)
                );
            } else {
                $priceXmlObj->addAttribute(
                    'as_low_as', $_coreHelper->currency($_minimalPriceTax, true, false)
                );
                if ($_weeeTaxAmount && $product->getPriceType() == 1
                    && $_weeeHelper->typeOfDisplay($product, array(2, 1, 4))
                ) {
                    $weeeXmlObj = $priceXmlObj->addChild('weee');
                    $_weeeSeparator = ' + ';
                    $weeeXmlObj->addAttribute('separator', $_weeeSeparator);
                    foreach ($_weeeTaxAttributes as $_weeeTaxAttribute) {
                        if ($_weeeHelper->typeOfDisplay($product, array(2, 4))) {
                            $amount = $_weeeTaxAttribute->getAmount() + $_weeeTaxAttribute->getTaxAmount();
                        } else {
                            $amount = $_weeeTaxAttribute->getAmount();
                        }
                        $weeeItemXmlObj = $weeeXmlObj->addChild('item');
                        $weeeItemXmlObj->addAttribute(
                            'name', $weeeItemXmlObj->xmlentities($_weeeTaxAttribute->getName())
                        );
                        $weeeItemXmlObj->addAttribute(
                            'amount', $_coreHelper->currency($amount, true, false)
                        );
                    }
                }
                if ($_weeeHelper->typeOfDisplay($product, 2) && $_weeeTaxAmount) {
                    $priceXmlObj->addAttribute(
                        'as_low_as_including_tax', $_coreHelper->currency($_minimalPriceInclTax, true, false)
                    );
                }
            }
        /**
         * if ($product->getPriceView()) {
         */
        } else {
            if ($_minimalPrice <> $_maximalPrice) {
                if ($_taxHelper->displayBothPrices()) {
                    $priceXmlObj->addAttribute(
                        'from_excluding_tax', $_coreHelper->currency($_minimalPriceTax, true, false)
                    );
                    if ($_weeeTaxAmount && $product->getPriceType() == 1
                        && $_weeeHelper->typeOfDisplay($product, array(2, 1, 4))
                    ) {
                        $weeeXmlObj = $priceXmlObj->addChild('from_weee');
                        $_weeeSeparator = ' + ';
                        $weeeXmlObj->addAttribute('separator', $_weeeSeparator);
                        foreach ($_weeeTaxAttributes as $_weeeTaxAttribute) {
                            if ($_weeeHelper->typeOfDisplay($product, array(2, 4))) {
                                $amount = $_weeeTaxAttribute->getAmount() + $_weeeTaxAttribute->getTaxAmount();
                            } else {
                                $amount = $_weeeTaxAttribute->getAmount();
                            }
                            $weeeItemXmlObj = $weeeXmlObj->addChild('item');
                            $weeeItemXmlObj->addAttribute(
                                'name', $weeeItemXmlObj->xmlentities($_weeeTaxAttribute->getName())
                            );
                            $weeeItemXmlObj->addAttribute(
                                'amount', $_coreHelper->currency($amount, true, false)
                            );
                        }
                    }
                    $priceXmlObj->addAttribute(
                        'from_including_tax', $_coreHelper->currency($_minimalPriceInclTax, true, false)
                    );
                } else {
                    $priceXmlObj->addAttribute('from', $_coreHelper->currency($_minimalPriceTax, true, false));
                    if ($_weeeTaxAmount && $product->getPriceType() == 1
                        && $_weeeHelper->typeOfDisplay($product, array(2, 1, 4))
                    ) {
                        $weeeXmlObj = $priceXmlObj->addChild('from_weee');
                        $_weeeSeparator = ' + ';
                        $weeeXmlObj->addAttribute('separator', $_weeeSeparator);
                        foreach ($_weeeTaxAttributes as $_weeeTaxAttribute) {
                            if ($_weeeHelper->typeOfDisplay($product, array(2, 4))) {
                                $amount = $_weeeTaxAttribute->getAmount() + $_weeeTaxAttribute->getTaxAmount();
                            } else {
                                $amount = $_weeeTaxAttribute->getAmount();
                            }
                            $weeeItemXmlObj = $weeeXmlObj->addChild('item');
                            $weeeItemXmlObj->addAttribute(
                                'name', $weeeItemXmlObj->xmlentities($_weeeTaxAttribute->getName())
                            );
                            $weeeItemXmlObj->addAttribute('amount', $_coreHelper->currency($amount, true, false));
                        }
                    }
                    if ($_weeeHelper->typeOfDisplay($product, 2) && $_weeeTaxAmount) {
                        $priceXmlObj->addAttribute(
                            'from_including_tax', $_coreHelper->currency($_minimalPriceInclTax, true, false)
                        );
                    }
                }

                $_maximalPriceTax = Mage::helper('Mage_Tax_Helper_Data')->getPrice($product, $_maximalPrice);
                $_maximalPriceInclTax = Mage::helper('Mage_Tax_Helper_Data')->getPrice($product, $_maximalPrice, true);

                if ($product->getPriceType() == 1) {
                    if ($_weeeTaxAmount && $_weeeHelper->typeOfDisplay($product, array(0, 1, 4))) {
                        $_maximalPriceTax += $_weeeTaxAmount;
                        $_maximalPriceInclTax += $_weeeTaxAmount;
                    }
                    if ($_weeeTaxAmount && $_weeeHelper->typeOfDisplay($product, 2)) {
                        $_maximalPriceInclTax += $_weeeTaxAmount;
                    }
                }

                if ($_taxHelper->displayBothPrices()) {
                    $priceXmlObj->addAttribute(
                        'to_excluding_tax', $_coreHelper->currency($_maximalPriceTax, true, false)
                    );
                    if ($_weeeTaxAmount && $product->getPriceType() == 1
                        && $_weeeHelper->typeOfDisplay($product, array(2, 1, 4))
                    ) {
                        $weeeXmlObj = $priceXmlObj->addChild('to_weee');
                        $_weeeSeparator = ' + ';
                        $weeeXmlObj->addAttribute('separator', $_weeeSeparator);
                        foreach ($_weeeTaxAttributes as $_weeeTaxAttribute) {
                            if ($_weeeHelper->typeOfDisplay($product, array(2, 4))) {
                                $amount = $_weeeTaxAttribute->getAmount() + $_weeeTaxAttribute->getTaxAmount();
                            } else {
                                $amount = $_weeeTaxAttribute->getAmount();
                            }
                            $weeeItemXmlObj = $weeeXmlObj->addChild('item');
                            $weeeItemXmlObj->addAttribute(
                                'name', $weeeItemXmlObj->xmlentities($_weeeTaxAttribute->getName())
                            );
                            $weeeItemXmlObj->addAttribute(
                                'amount', $_coreHelper->currency($amount, true, false)
                            );
                        }
                    }
                    $priceXmlObj->addAttribute(
                        'to_including_tax', $_coreHelper->currency($_maximalPriceInclTax, true, false)
                    );
                } else {
                    $priceXmlObj->addAttribute(
                        'to', $_coreHelper->currency($_maximalPriceTax, true, false)
                    );
                    if ($_weeeTaxAmount && $product->getPriceType() == 1
                        && $_weeeHelper->typeOfDisplay($product, array(2, 1, 4))
                    ) {
                        $weeeXmlObj = $priceXmlObj->addChild('to_weee');
                        $_weeeSeparator = ' + ';
                        $weeeXmlObj->addAttribute('separator', $_weeeSeparator);
                        foreach ($_weeeTaxAttributes as $_weeeTaxAttribute) {
                            if ($_weeeHelper->typeOfDisplay($product, array(2, 4))) {
                                $amount = $_weeeTaxAttribute->getAmount() + $_weeeTaxAttribute->getTaxAmount();
                            } else {
                                $amount = $_weeeTaxAttribute->getAmount();
                            }
                            $weeeItemXmlObj = $weeeXmlObj->addChild('item');
                            $weeeItemXmlObj->addAttribute(
                                'name', $weeeItemXmlObj->xmlentities($_weeeTaxAttribute->getName())
                            );
                            $weeeItemXmlObj->addAttribute(
                                'amount', $_coreHelper->currency($amount, true, false)
                            );
                        }
                    }
                    if ($_weeeHelper->typeOfDisplay($product, 2) && $_weeeTaxAmount) {
                        $priceXmlObj->addAttribute(
                            'to_including_tax', $_coreHelper->currency($_maximalPriceInclTax, true, false)
                        );
                    }
                }
            /**
             * if ($_minimalPrice <> $_maximalPrice) {
             */
            } else {
                if ($_taxHelper->displayBothPrices()) {
                    $priceXmlObj->addAttribute(
                        'excluding_tax', $_coreHelper->currency($_minimalPriceTax, true, false)
                    );
                    if ($_weeeTaxAmount && $product->getPriceType() == 1
                        && $_weeeHelper->typeOfDisplay($product, array(2, 1, 4))
                    ) {
                        $weeeXmlObj = $priceXmlObj->addChild('weee');
                        $_weeeSeparator = ' + ';
                        $weeeXmlObj->addAttribute('separator', $_weeeSeparator);
                        foreach ($_weeeTaxAttributes as $_weeeTaxAttribute) {
                            if ($_weeeHelper->typeOfDisplay($product, array(2, 4))) {
                                $amount = $_weeeTaxAttribute->getAmount() + $_weeeTaxAttribute->getTaxAmount();
                            } else {
                                $amount = $_weeeTaxAttribute->getAmount();
                            }
                            $weeeItemXmlObj = $weeeXmlObj->addChild('item');
                            $weeeItemXmlObj->addAttribute(
                                'name', $weeeItemXmlObj->xmlentities($_weeeTaxAttribute->getName())
                            );
                            $weeeItemXmlObj->addAttribute(
                                'amount', $_coreHelper->currency($amount, true, false)
                            );
                        }
                    }
                    $priceXmlObj->addAttribute(
                        'including_tax', $_coreHelper->currency($_minimalPriceInclTax, true, false)
                    );
                } else {
                    $priceXmlObj->addAttribute(
                        'regular', $_coreHelper->currency($_minimalPriceTax, true, false)
                    );
                    if ($_weeeTaxAmount && $product->getPriceType() == 1
                        && $_weeeHelper->typeOfDisplay($product, array(2, 1, 4))
                    ) {
                        $weeeXmlObj = $priceXmlObj->addChild('weee');
                        $_weeeSeparator = ' + ';
                        $weeeXmlObj->addAttribute('separator', $_weeeSeparator);
                        foreach ($_weeeTaxAttributes as $_weeeTaxAttribute) {
                            if ($_weeeHelper->typeOfDisplay($product, array(2, 4))) {
                                $amount = $_weeeTaxAttribute->getAmount() + $_weeeTaxAttribute->getTaxAmount();
                            } else {
                                $amount = $_weeeTaxAttribute->getAmount();
                            }
                            $weeeItemXmlObj = $weeeXmlObj->addChild('item');
                            $weeeItemXmlObj->addAttribute(
                                'name', $weeeItemXmlObj->xmlentities($_weeeTaxAttribute->getName())
                            );
                            $weeeItemXmlObj->addAttribute(
                                'amount', $_coreHelper->currency($amount, true, false)
                            );
                        }
                    }
                    if ($_weeeHelper->typeOfDisplay($product, 2) && $_weeeTaxAmount) {
                        $priceXmlObj->addAttribute(
                            'including_tax', $_coreHelper->currency($_minimalPriceInclTax, true, false)
                        );
                    }
                }
            }
        }
    }

    /**
     * Get tier prices (formatted)
     *
     * @param Mage_Catalog_Model_Product $product
     * @return array
     */
    protected function _getTierPrices($product)
    {
        if (is_null($product)) {
            return array();
        }
        $prices  = $product->getFormatedTierPrice();

        $res = array();
        if (is_array($prices)) {
            foreach ($prices as $price) {
                $price['price_qty'] = $price['price_qty']*1;
                $price['savePercent'] = ceil(100 - $price['price']);
                $price['formated_price'] = Mage::app()->getStore()->formatPrice(
                    Mage::app()->getStore()->convertPrice(
                        Mage::helper('Mage_Tax_Helper_Data')->getPrice($product, $price['website_price'])
                    ),
                    false
                );
                $price['formated_price_incl_tax'] = Mage::app()->getStore()->formatPrice(
                    Mage::app()->getStore()->convertPrice(
                        Mage::helper('Mage_Tax_Helper_Data')->getPrice($product, $price['website_price'], true)
                    ),
                    false
                );
                $res[] = $price;
            }
        }

        return $res;
    }
}
