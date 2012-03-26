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
 * @package     Mage_Sales
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Mage_Sales_Model_Quote_Address_Total_Tax extends Mage_Sales_Model_Quote_Address_Total_Abstract
{
    protected $_appliedTaxes = array();

    public function __construct(){
        $this->setCode('tax');
    }

    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        $store = $address->getQuote()->getStore();

        $address->setTaxAmount(0);
        $address->setBaseTaxAmount(0);
        //$address->setShippingTaxAmount(0);
        //$address->setBaseShippingTaxAmount(0);
        $address->setAppliedTaxes(array());

        $items = $address->getAllItems();
        if (!count($items)) {
            return $this;
        }
        $custTaxClassId = $address->getQuote()->getCustomerTaxClassId();

        $taxCalculationModel = Mage::getSingleton('Mage_Tax_Model_Calculation');
        /* @var $taxCalculationModel Mage_Tax_Model_Calculation */
        $request = $taxCalculationModel->getRateRequest(
            $address,
            $address->getQuote()->getBillingAddress(),
            $custTaxClassId,
            $store
        );

        foreach ($items as $item) {
            /**
             * Child item's tax we calculate for parent
             */
            if ($item->getParentItemId()) {
                continue;
            }
            /**
             * We calculate parent tax amount as sum of children's tax amounts
             */

            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                foreach ($item->getChildren() as $child) {
                    $discountBefore = $item->getDiscountAmount();
                    $baseDiscountBefore = $item->getBaseDiscountAmount();

                    $rate = $taxCalculationModel->getRate(
                        $request->setProductClassId($child->getProduct()->getTaxClassId())
                    );

                    $child->setTaxPercent($rate);
                    $child->calcTaxAmount();

                    if ($discountBefore != $item->getDiscountAmount()) {
                        $address->setDiscountAmount(
                            $address->getDiscountAmount() + ($item->getDiscountAmount() - $discountBefore)
                        );
                        $address->setBaseDiscountAmount(
                            $address->getBaseDiscountAmount() + ($item->getBaseDiscountAmount() - $baseDiscountBefore)
                        );

                        $address->setGrandTotal(
                            $address->getGrandTotal() - ($item->getDiscountAmount() - $discountBefore)
                        );
                        $address->setBaseGrandTotal(
                            $address->getBaseGrandTotal() - ($item->getBaseDiscountAmount() - $baseDiscountBefore)
                        );
                    }

                    $this->_saveAppliedTaxes(
                       $address,
                       $taxCalculationModel->getAppliedRates($request),
                       $child->getTaxAmount(),
                       $child->getBaseTaxAmount(),
                       $rate
                    );
                }
                $itemTaxAmount = $item->getTaxAmount() + $item->getDiscountTaxCompensation();
                $address->setTaxAmount($address->getTaxAmount() + $itemTaxAmount);
                $itemBaseTaxAmount = $item->getBaseTaxAmount() + $item->getBaseDiscountTaxCompensation();
                $address->setBaseTaxAmount($address->getBaseTaxAmount() + $itemBaseTaxAmount);
            }
            else {
                $discountBefore = $item->getDiscountAmount();
                $baseDiscountBefore = $item->getBaseDiscountAmount();

                $rate = $taxCalculationModel->getRate(
                    $request->setProductClassId($item->getProduct()->getTaxClassId())
                );

                $item->setTaxPercent($rate);
                $item->calcTaxAmount();

                if ($discountBefore != $item->getDiscountAmount()) {
                    $address->setDiscountAmount(
                        $address->getDiscountAmount() + ($item->getDiscountAmount() - $discountBefore)
                    );
                    $address->setBaseDiscountAmount(
                        $address->getBaseDiscountAmount() + ($item->getBaseDiscountAmount() - $baseDiscountBefore)
                    );

                    $address->setGrandTotal(
                        $address->getGrandTotal() - ($item->getDiscountAmount() - $discountBefore)
                    );
                    $address->setBaseGrandTotal(
                        $address->getBaseGrandTotal() - ($item->getBaseDiscountAmount() - $baseDiscountBefore)
                    );
                }

                $itemTaxAmount = $item->getTaxAmount() + $item->getDiscountTaxCompensation();
                $address->setTaxAmount($address->getTaxAmount() + $itemTaxAmount);
                $itemBaseTaxAmount = $item->getBaseTaxAmount() + $item->getBaseDiscountTaxCompensation();
                $address->setBaseTaxAmount($address->getBaseTaxAmount() + $itemBaseTaxAmount);

                $applied = $taxCalculationModel->getAppliedRates($request);
                $this->_saveAppliedTaxes(
                   $address,
                   $applied,
                   $item->getTaxAmount(),
                   $item->getBaseTaxAmount(),
                   $rate
                );
            }
        }


        $shippingTaxClass = Mage::getStoreConfig(Mage_Tax_Model_Config::CONFIG_XML_PATH_SHIPPING_TAX_CLASS, $store);

        $shippingTax      = 0;
        $shippingBaseTax  = 0;

        if ($shippingTaxClass) {
            if ($rate = $taxCalculationModel->getRate($request->setProductClassId($shippingTaxClass))) {
                if (!Mage::helper('Mage_Tax_Helper_Data')->shippingPriceIncludesTax()) {
                    $shippingTax    = $address->getShippingAmount() * $rate/100;
                    $shippingBaseTax= $address->getBaseShippingAmount() * $rate/100;
                } else {
                    $shippingTax    = $address->getShippingTaxAmount();
                    $shippingBaseTax= $address->getBaseShippingTaxAmount();
                }

                $shippingTax    = $store->roundPrice($shippingTax);
                $shippingBaseTax= $store->roundPrice($shippingBaseTax);

                $address->setTaxAmount($address->getTaxAmount() + $shippingTax);
                $address->setBaseTaxAmount($address->getBaseTaxAmount() + $shippingBaseTax);

                $this->_saveAppliedTaxes(
                    $address,
                    $taxCalculationModel->getAppliedRates($request),
                    $shippingTax,
                    $shippingBaseTax,
                    $rate
                );
            }
        }

        if (!Mage::helper('Mage_Tax_Helper_Data')->shippingPriceIncludesTax()) {
            $address->setShippingTaxAmount($shippingTax);
            $address->setBaseShippingTaxAmount($shippingBaseTax);
        }

        $address->setGrandTotal($address->getGrandTotal() + $address->getTaxAmount());
        $address->setBaseGrandTotal($address->getBaseGrandTotal() + $address->getBaseTaxAmount());
        return $this;
    }

    protected function _saveAppliedTaxes(Mage_Sales_Model_Quote_Address $address, $applied, $amount, $baseAmount, $rate)
    {
        $previouslyAppliedTaxes = $address->getAppliedTaxes();
        $process = count($previouslyAppliedTaxes);

        foreach ($applied as $row) {
            if (!isset($previouslyAppliedTaxes[$row['id']])) {
                $row['process'] = $process;
                $row['amount'] = 0;
                $row['base_amount'] = 0;
                $previouslyAppliedTaxes[$row['id']] = $row;
            }

            if (!is_null($row['percent'])) {
                $row['percent'] = $row['percent'] ? $row['percent'] : 1;
                $rate = $rate ? $rate : 1;

                $appliedAmount = $amount/$rate*$row['percent'];
                $baseAppliedAmount = $baseAmount/$rate*$row['percent'];
            } else {
                $appliedAmount = 0;
                $baseAppliedAmount = 0;
                foreach ($row['rates'] as $rate) {
                    $appliedAmount += $rate['amount'];
                    $baseAppliedAmount += $rate['base_amount'];
                }
            }


            if ($appliedAmount || $previouslyAppliedTaxes[$row['id']]['amount']) {
                $previouslyAppliedTaxes[$row['id']]['amount'] += $appliedAmount;
                $previouslyAppliedTaxes[$row['id']]['base_amount'] += $baseAppliedAmount;
            } else {
                unset($previouslyAppliedTaxes[$row['id']]);
            }
        }
        $address->setAppliedTaxes($previouslyAppliedTaxes);
    }

    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
        $applied = $address->getAppliedTaxes();
        $store = $address->getQuote()->getStore();
        $amount = $address->getTaxAmount();

        if (($amount!=0) || (Mage::helper('Mage_Tax_Helper_Data')->displayZeroTax($store))) {
            $address->addTotal(array(
                'code'=>$this->getCode(),
                'title'=>Mage::helper('Mage_Sales_Helper_Data')->__('Tax'),
                'full_info'=>$applied ? $applied : array(),
                'value'=>$amount
            ));
        }
        return $this;
    }
}
