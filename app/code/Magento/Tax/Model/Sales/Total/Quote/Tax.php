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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Tax\Model\Sales\Total\Quote;

use Magento\Store\Model\Store;
use Magento\Sales\Model\Quote\Address;
use Magento\Sales\Model\Quote\Address\Total\AbstractTotal;
use Magento\Sales\Model\Quote\Item\AbstractItem;
use Magento\Tax\Model\Calculation;

/**
 * Tax totals calculation model
 */
class Tax extends AbstractTotal
{
    /**
     * Tax module helper
     *
     * @var \Magento\Tax\Helper\Data
     */
    protected $_taxData;

    /**
     * Tax calculation model
     *
     * @var \Magento\Tax\Model\Calculation
     */
    protected $_calculator;

    /**
     * Tax configuration object
     *
     * @var \Magento\Tax\Model\Config
     */
    protected $_config;

    /**
     * Flag which is initialized when collect method is start.
     * Is used for checking if store tax and customer tax requests are similar
     *
     * @var bool
     */
    protected $_areTaxRequestsSimilar = false;

    /**
     * @var array
     */
    protected $_roundingDeltas = array();

    /**
     * @var array
     */
    protected $_baseRoundingDeltas = array();

    /**
     * @var Store
     */
    protected $_store;

    /**
     * Hidden taxes array
     *
     * @var array
     */
    protected $_hiddenTaxes = array();

    /**
     * Class constructor
     *
     * @param \Magento\Tax\Helper\Data $taxData
     * @param \Magento\Tax\Model\Calculation $calculation
     * @param \Magento\Tax\Model\Config $taxConfig
     */
    public function __construct(
        \Magento\Tax\Helper\Data $taxData,
        \Magento\Tax\Model\Calculation $calculation,
        \Magento\Tax\Model\Config $taxConfig
    ) {
        $this->setCode('tax');
        $this->_taxData = $taxData;
        $this->_calculator = $calculation;
        $this->_config = $taxConfig;
    }

    /**
     * Collect tax totals for quote address
     *
     * @param   Address $address
     * @return  $this
     */
    public function collect(Address $address)
    {
        parent::collect($address);
        $this->_roundingDeltas = array();
        $this->_baseRoundingDeltas = array();
        $this->_hiddenTaxes = array();
        $address->setShippingTaxAmount(0);
        $address->setBaseShippingTaxAmount(0);

        $this->_store = $address->getQuote()->getStore();
        $customerData = $address->getQuote()->getCustomerData();
        if ($customerData) {
            $this->_calculator->setCustomerData($address->getQuote()->getCustomerData());
        }

        if (!$address->getAppliedTaxesReset()) {
            $address->setAppliedTaxes(array());
        }

        $items = $this->_getAddressItems($address);
        if (!count($items)) {
            return $this;
        }
        $request = $this->_calculator->getRateRequest(
            $address,
            $address->getQuote()->getBillingAddress(),
            $address->getQuote()->getCustomerTaxClassId(),
            $this->_store
        );

        if ($this->_config->priceIncludesTax($this->_store)) {
            if ($this->_taxData->isCrossBorderTradeEnabled($this->_store)) {
                $this->_areTaxRequestsSimilar = true;
            } else {
                $this->_areTaxRequestsSimilar = $this->_calculator->compareRequests(
                    $this->_calculator->getRateOriginRequest($this->_store),
                    $request
                );
            }
        }

        switch ($this->_config->getAlgorithm($this->_store)) {
            case Calculation::CALC_UNIT_BASE:
                $this->_unitBaseCalculation($address, $request);
                break;
            case Calculation::CALC_ROW_BASE:
                $this->_rowBaseCalculation($address, $request);
                break;
            case Calculation::CALC_TOTAL_BASE:
                $this->_totalBaseCalculation($address, $request);
                break;
            default:
                break;
        }

        $this->_addAmount($address->getExtraTaxAmount());
        $this->_addBaseAmount($address->getBaseExtraTaxAmount());
        $this->_calculateShippingTax($address, $request);

        $this->_processHiddenTaxes();

        return $this;
    }

    /**
     * Process hidden taxes for items and shippings (in accordance with hidden tax type)
     *
     * @return void
     */
    protected function _processHiddenTaxes()
    {
        $this->_getAddress()->setTotalAmount('hidden_tax', 0);
        $this->_getAddress()->setBaseTotalAmount('hidden_tax', 0);
        $this->_getAddress()->setTotalAmount('shipping_hidden_tax', 0);
        $this->_getAddress()->setBaseTotalAmount('shipping_hidden_tax', 0);
        foreach ($this->_hiddenTaxes as $taxInfoItem) {
            if (isset($taxInfoItem['item'])) {
                // Item hidden taxes
                $item = $taxInfoItem['item'];
                $hiddenTax = $taxInfoItem['value'];
                $baseHiddenTax = $taxInfoItem['base_value'];
                $qty = $taxInfoItem['qty'];

                $item->setHiddenTaxAmount(max(0, $qty * $hiddenTax));
                $item->setBaseHiddenTaxAmount(max(0, $qty * $baseHiddenTax));
                $this->_getAddress()->addTotalAmount('hidden_tax', $item->getHiddenTaxAmount());
                $this->_getAddress()->addBaseTotalAmount('hidden_tax', $item->getBaseHiddenTaxAmount());
            } else {
                // Shipping hidden taxes
                $hiddenTax = $taxInfoItem['value'];
                $baseHiddenTax = $taxInfoItem['base_value'];

                $this->_getAddress()->setShippingHiddenTaxAmount(max(0, $hiddenTax));
                $this->_getAddress()->setBaseShippingHiddenTaxAmnt(max(0, $baseHiddenTax));
                $this->_getAddress()->addTotalAmount('shipping_hidden_tax', $hiddenTax);
                $this->_getAddress()->addBaseTotalAmount('shipping_hidden_tax', $baseHiddenTax);
            }
        }
    }

    /**
     * Calculate shipping tax for a single tax rate
     *
     * @param Address $address
     * @param float $rate
     * @param array $appliedRates
     * @param string $taxId
     * @return $this
     */
    protected function _calculateShippingTaxByRate(
        $address,
        $rate,
        $appliedRates,
        $taxId = null
    ) {
        $inclTax = $address->getIsShippingInclTax();
        $shipping = $address->getShippingTaxable();
        $baseShipping = $address->getBaseShippingTaxable();
        $rateKey = ($taxId == null) ? (string)$rate : $taxId;

        $hiddenTax = null;
        $baseHiddenTax = null;
        switch ($this->_taxData->getCalculationSequence($this->_store)) {
            case Calculation::CALC_TAX_BEFORE_DISCOUNT_ON_EXCL:
            case Calculation::CALC_TAX_BEFORE_DISCOUNT_ON_INCL:
                $tax = $this->_calculator->calcTaxAmount($shipping, $rate, $inclTax, false);
                $baseTax = $this->_calculator->calcTaxAmount($baseShipping, $rate, $inclTax, false);
                break;
            case Calculation::CALC_TAX_AFTER_DISCOUNT_ON_EXCL:
            case Calculation::CALC_TAX_AFTER_DISCOUNT_ON_INCL:
                $discountAmount = $address->getShippingDiscountAmount();
                $baseDiscountAmount = $address->getBaseShippingDiscountAmount();
                $tax = $this->_calculator->calcTaxAmount(
                    $shipping - $discountAmount,
                    $rate,
                    $inclTax,
                    false
                );
                $baseTax = $this->_calculator->calcTaxAmount(
                    $baseShipping - $baseDiscountAmount,
                    $rate,
                    $inclTax,
                    false
                );
                break;
        }

        if ($this->_config->getAlgorithm($this->_store) == Calculation::CALC_TOTAL_BASE) {
            $tax = $this->_deltaRound($tax, $rateKey, $inclTax);
            $baseTax = $this->_deltaRound($baseTax, $rateKey, $inclTax, 'base');
            $this->_addAmount(max(0, $tax));
            $this->_addBaseAmount(max(0, $baseTax));
        } else {
            $tax = $this->_calculator->round($tax);
            $baseTax = $this->_calculator->round($baseTax);
            $this->_addAmount(max(0, $tax));
            $this->_addBaseAmount(max(0, $baseTax));
        }

        if ($inclTax && !empty($discountAmount)) {
            $taxBeforeDiscount = $this->_calculator->calcTaxAmount(
                $shipping,
                $rate,
                $inclTax,
                false
            );
            $baseTaxBeforeDiscount = $this->_calculator->calcTaxAmount(
                $baseShipping,
                $rate,
                $inclTax,
                false
            );
            if ($this->_config->getAlgorithm($this->_store) == Calculation::CALC_TOTAL_BASE) {
                $taxBeforeDiscount = $this->_deltaRound(
                    $taxBeforeDiscount,
                    $rateKey,
                    $inclTax,
                    'tax_before_discount'
                );
                $baseTaxBeforeDiscount = $this->_deltaRound(
                    $baseTaxBeforeDiscount,
                    $rateKey,
                    $inclTax,
                    'tax_before_discount_base'
                );
            } else {
                $taxBeforeDiscount = $this->_calculator->round($taxBeforeDiscount);
                $baseTaxBeforeDiscount = $this->_calculator->round($baseTaxBeforeDiscount);
            }
            $hiddenTax = max(0, $taxBeforeDiscount - max(0, $tax));
            $baseHiddenTax = max(0, $baseTaxBeforeDiscount - max(0, $baseTax));
            $this->_hiddenTaxes[] = array(
                'rate_key' => $rateKey,
                'value' => $hiddenTax,
                'base_value' => $baseHiddenTax,
                'incl_tax' => $inclTax,
            );
        }

        $address->setShippingTaxAmount($address->getShippingTaxAmount() + max(0, $tax));
        $address->setBaseShippingTaxAmount($address->getBaseShippingTaxAmount() + max(0, $baseTax));
        $this->_saveAppliedTaxes($address, $appliedRates, $tax, $baseTax, $rate);

        return $this;
    }

    /**
     * Tax calculation for shipping price
     *
     * @param   Address $address
     * @param   \Magento\Framework\Object $taxRateRequest
     * @return  $this
     */
    protected function _calculateShippingTax(
        Address $address,
        \Magento\Framework\Object $taxRateRequest
    ) {
        $taxRateRequest->setProductClassId($this->_config->getShippingTaxClass($this->_store));
        $rate = $this->_calculator->getRate($taxRateRequest);
        $inclTax = $address->getIsShippingInclTax();

        $address->setShippingTaxAmount(0);
        $address->setBaseShippingTaxAmount(0);
        $address->setShippingHiddenTaxAmount(0);
        $address->setBaseShippingHiddenTaxAmount(0);
        $appliedRates = $this->_calculator->getAppliedRates($taxRateRequest);
        if ($inclTax) {
            $this->_calculateShippingTaxByRate($address, $rate, $appliedRates);
        } else {
            foreach ($appliedRates as $appliedRate) {
                $taxRate = $appliedRate['percent'];
                $taxId = $appliedRate['id'];
                $this->_calculateShippingTaxByRate($address, $taxRate, array($appliedRate), $taxId);
            }
        }
        return $this;
    }

    /**
     * Initialize tax related fields in item
     *
     * @param AbstractItem $item
     * @param array $appliedRates
     * @param float $rate
     * @param bool $isUnitBasedCalculation
     * @return bool
     */
    protected function initializeItemTax(
        AbstractItem $item,
        $appliedRates,
        $rate,
        $isUnitBasedCalculation = false
    ) {
        $item->setTaxAmount(0);
        $item->setBaseTaxAmount(0);
        $item->setHiddenTaxAmount(0);
        $item->setBaseHiddenTaxAmount(0);
        $item->setTaxPercent($rate);
        $item->setDiscountTaxCompensation(0);
        $rowTotalInclTax = $item->getRowTotalInclTax();
        $recalculateRowTotalInclTax = false;
        if (!isset($rowTotalInclTax)) {
            if ($isUnitBasedCalculation) {
                $qty = $item->getTotalQty();
                $item->setRowTotalInclTax($this->_store->roundPrice($item->getTaxableAmount() * $qty));
                $item->setBaseRowTotalInclTax($this->_store->roundPrice($item->getBaseTaxableAmount() * $qty));
            } else {
                $item->setRowTotalInclTax($item->getTaxableAmount());
                $item->setBaseRowTotalInclTax($item->getBaseTaxableAmount());
            }
            $recalculateRowTotalInclTax = true;
        }
        $item->setTaxRates($appliedRates);

        return $recalculateRowTotalInclTax;
    }

    /**
     *
     * @param Address $address
     * @param AbstractItem $item
     * @param \Magento\Framework\Object $taxRateRequest
     * @param array $itemTaxGroups
     * @param boolean $catalogPriceInclTax
     * @return $this
     */
    protected function _unitBaseProcessItemTax(
        Address $address,
        AbstractItem $item,
        \Magento\Framework\Object $taxRateRequest,
        &$itemTaxGroups,
        $catalogPriceInclTax
    ) {
        $taxRateRequest->setProductClassId($item->getProduct()->getTaxClassId());
        $appliedRates = $this->_calculator->getAppliedRates($taxRateRequest);
        $rate = $this->_calculator->getRate($taxRateRequest);

        $recalculateRowTotalInclTax = $this->initializeItemTax($item, $appliedRates, $rate, true);

        if ($catalogPriceInclTax) {
            $this->_calcUnitTaxAmount($item, $rate);
            $this->_saveAppliedTaxes(
                $address,
                $appliedRates,
                $item->getTaxAmount(),
                $item->getBaseTaxAmount(),
                $rate
            );
        } else {
            //need to calculate each tax separately
            $taxGroups = array();
            foreach ($appliedRates as $appliedTax) {
                $taxId = $appliedTax['id'];
                $taxRate = $appliedTax['percent'];
                $this->_calcUnitTaxAmount($item, $taxRate, $taxGroups, $taxId, $recalculateRowTotalInclTax);
                $this->_saveAppliedTaxes(
                    $address,
                    array($appliedTax),
                    $taxGroups[$taxId]['tax'],
                    $taxGroups[$taxId]['base_tax'],
                    $taxRate
                );
            }
        }
        if ($rate > 0) {
            $itemTaxGroups[$item->getId()] = $appliedRates;
        }
        $this->_addAmount($item->getTaxAmount());
        $this->_addBaseAmount($item->getBaseTaxAmount());
        return $this;
    }

    /**
     * Calculate address tax amount based on one unit price and tax amount
     *
     * @param Address $address
     * @param \Magento\Framework\Object $taxRateRequest
     * @return $this
     */
    protected function _unitBaseCalculation(
        Address $address,
        \Magento\Framework\Object $taxRateRequest
    ) {
        $items = $this->_getAddressItems($address);
        $itemTaxGroups = array();
        $store = $address->getQuote()->getStore();
        $catalogPriceInclTax = $this->_config->priceIncludesTax($store);

        foreach ($items as $item) {
            if ($item->getParentItem()) {
                continue;
            }

            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                foreach ($item->getChildren() as $child) {
                    $this->_unitBaseProcessItemTax(
                        $address,
                        $child,
                        $taxRateRequest,
                        $itemTaxGroups,
                        $catalogPriceInclTax
                    );
                }
                $this->_recalculateParent($item);
            } else {
                $this->_unitBaseProcessItemTax(
                    $address,
                    $item,
                    $taxRateRequest,
                    $itemTaxGroups,
                    $catalogPriceInclTax
                );
            }
        }
        if ($address->getQuote()->getTaxesForItems()) {
            $itemTaxGroups += $address->getQuote()->getTaxesForItems();
        }
        $address->getQuote()->setTaxesForItems($itemTaxGroups);
        return $this;
    }

    /**
     * Calculate unit tax amount based on unit price
     *
     * @param   AbstractItem $item
     * @param   float $rate
     * @param   array $taxGroups
     * @param   string $taxId
     * @param   bool $recalculateRowTotalInclTax
     * @return  $this
     */
    protected function _calcUnitTaxAmount(
        AbstractItem $item,
        $rate,
        &$taxGroups = null,
        $taxId = null,
        $recalculateRowTotalInclTax = false
    ) {
        $qty = $item->getTotalQty();
        $inclTax = $item->getIsPriceInclTax();
        $price = $item->getTaxableAmount();
        $basePrice = $item->getBaseTaxableAmount();
        $extraTaxableAmount =  $item->getExtraTaxableAmount();
        $baseExtraTaxableAmount =  $item->getBaseExtraTaxableAmount();
        $rateKey = ($taxId == null) ? (string)$rate : $taxId;

        $unitTaxBeforeDiscount = 0;
        $baseUnitTaxBeforeDiscount = 0;
        switch ($this->_config->getCalculationSequence($this->_store)) {
            case Calculation::CALC_TAX_BEFORE_DISCOUNT_ON_EXCL:
            case Calculation::CALC_TAX_BEFORE_DISCOUNT_ON_INCL:
                $unitTaxBeforeDiscount = $this->_calculator->calcTaxAmount($price, $rate, $inclTax, false);
                $baseUnitTaxBeforeDiscount = $this->_calculator->calcTaxAmount($basePrice, $rate, $inclTax, false);
                $unitTaxBeforeDiscount = $unitTax = $this->_calculator->round($unitTaxBeforeDiscount);
                $baseUnitTaxBeforeDiscount = $baseUnitTax = $this->_calculator->round($baseUnitTaxBeforeDiscount);
                break;
            case Calculation::CALC_TAX_AFTER_DISCOUNT_ON_EXCL:
            case Calculation::CALC_TAX_AFTER_DISCOUNT_ON_INCL:
                $discountAmount = $item->getDiscountAmount() / $qty;
                $baseDiscountAmount = $item->getBaseDiscountAmount() / $qty;

                $unitTaxBeforeDiscount = $this->_calculator->calcTaxAmount($price, $rate, $inclTax, false);
                $unitTaxDiscount = $this->_calculator->calcTaxAmount($discountAmount, $rate, $inclTax, false);
                $unitTax = $this->_calculator->round(max($unitTaxBeforeDiscount - $unitTaxDiscount, 0));

                $baseUnitTaxBeforeDiscount = $this->_calculator->calcTaxAmount($basePrice, $rate, $inclTax, false);
                $baseUnitTaxDiscount = $this->_calculator->calcTaxAmount($baseDiscountAmount, $rate, $inclTax, false);
                $baseUnitTax = $this->_calculator->round(max($baseUnitTaxBeforeDiscount - $baseUnitTaxDiscount, 0));

                $unitTax = $this->_calculator->round($unitTax);
                $baseUnitTax = $this->_calculator->round($baseUnitTax);

                $unitTaxBeforeDiscount = max(0, $this->_calculator->round($unitTaxBeforeDiscount));
                $baseUnitTaxBeforeDiscount = max(0, $this->_calculator->round($baseUnitTaxBeforeDiscount));

                if ($inclTax && $discountAmount > 0) {
                    $hiddenTax = $unitTaxBeforeDiscount - $unitTax;
                    $baseHiddenTax = $baseUnitTaxBeforeDiscount - $baseUnitTax;
                    $this->_hiddenTaxes[] = array(
                        'rate_key' => $rateKey,
                        'qty' => $qty,
                        'item' => $item,
                        'value' => $hiddenTax,
                        'base_value' => $baseHiddenTax,
                        'incl_tax' => $inclTax
                    );
                } elseif ($discountAmount > $price) {
                    // case with 100% discount on price incl. tax
                    $hiddenTax = $discountAmount - $price;
                    $baseHiddenTax = $baseDiscountAmount - $basePrice;
                    $this->_hiddenTaxes[] = array(
                        'rate_key' => $rateKey,
                        'qty' => $qty,
                        'item' => $item,
                        'value' => $hiddenTax,
                        'base_value' => $baseHiddenTax,
                        'incl_tax' => $inclTax
                    );
                }
                break;
        }
        $rowTax = $this->_store->roundPrice(max(0, $qty * $unitTax));
        $baseRowTax = $this->_store->roundPrice(max(0, $qty * $baseUnitTax));

        $extraTaxAmount = $this->_calculator->calcTaxAmount($extraTaxableAmount, $rate, $inclTax, true);
        $rowExtraTaxAmount = $extraTaxAmount * $qty;
        $baseExtraTaxAmount = $this->_calculator->calcTaxAmount($baseExtraTaxableAmount, $rate, $inclTax, true);
        $rowBaseExtraTaxAmount = $baseExtraTaxAmount * $qty;
        $rowTax += $rowExtraTaxAmount;
        $baseRowTax += $rowBaseExtraTaxAmount;
        $item->setTaxAmount($item->getTaxAmount() + $rowTax);
        $item->setBaseTaxAmount($item->getBaseTaxAmount() + $baseRowTax);
        if (is_array($taxGroups)) {
            $taxGroups[$rateKey]['tax'] = max(0, $rowTax);
            $taxGroups[$rateKey]['base_tax'] = max(0, $baseRowTax);
        }

        $rowTotalInclTax = $item->getRowTotalInclTax();
        if (!isset($rowTotalInclTax) || $recalculateRowTotalInclTax) {
            if ($this->_config->priceIncludesTax($this->_store)) {
                $item->setRowTotalInclTax($price * $qty);
                $item->setBaseRowTotalInclTax($basePrice * $qty);
            } else {
                $item->setRowTotalInclTax($item->getRowTotalInclTax() + $unitTaxBeforeDiscount * $qty);
                $item->setBaseRowTotalInclTax($item->getBaseRowTotalInclTax() + $baseUnitTaxBeforeDiscount * $qty);
            }
        }

        return $this;
    }

    /**
     *
     * @param Address $address
     * @param AbstractItem $item
     * @param \Magento\Framework\Object $taxRateRequest
     * @param array $itemTaxGroups
     * @param bool $catalogPriceInclTax
     * @return $this
     */
    protected function _rowBaseProcessItemTax(
        Address $address,
        AbstractItem $item,
        \Magento\Framework\Object $taxRateRequest,
        &$itemTaxGroups,
        $catalogPriceInclTax
    ) {
        $taxRateRequest->setProductClassId($item->getProduct()->getTaxClassId());
        $appliedRates = $this->_calculator->getAppliedRates($taxRateRequest);
        $rate = $this->_calculator->getRate($taxRateRequest);

        $recalculateRowTotalInclTax = $this->initializeItemTax($item, $appliedRates, $rate);

        if ($catalogPriceInclTax) {
            $this->_calcRowTaxAmount($item, $rate);
            $this->_saveAppliedTaxes($address, $appliedRates, $item->getTaxAmount(), $item->getBaseTaxAmount(), $rate);
        } else {
            //need to calculate each tax separately
            $taxGroups = array();
            foreach ($appliedRates as $appliedTax) {
                $taxId = $appliedTax['id'];
                $taxRate = $appliedTax['percent'];
                $this->_calcRowTaxAmount($item, $taxRate, $taxGroups, $taxId, $recalculateRowTotalInclTax);
                $this->_saveAppliedTaxes(
                    $address,
                    array($appliedTax),
                    $taxGroups[$taxId]['tax'],
                    $taxGroups[$taxId]['base_tax'],
                    $taxRate
                );
            }

        }
        if ($rate > 0) {
            $itemTaxGroups[$item->getId()] = $appliedRates;
        }
        $this->_addAmount($item->getTaxAmount());
        $this->_addBaseAmount($item->getBaseTaxAmount());
        return $this;
    }

    /**
     * Calculate address total tax based on row total
     *
     * @param   Address $address
     * @param   \Magento\Framework\Object $taxRateRequest
     * @return  $this
     */
    protected function _rowBaseCalculation(
        Address $address,
        \Magento\Framework\Object $taxRateRequest
    ) {
        $items = $this->_getAddressItems($address);
        $itemTaxGroups = array();
        $store = $address->getQuote()->getStore();
        $catalogPriceInclTax = $this->_config->priceIncludesTax($store);

        foreach ($items as $item) {
            if ($item->getParentItem()) {
                continue;
            }
            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                foreach ($item->getChildren() as $child) {
                    $this->_rowBaseProcessItemTax(
                        $address,
                        $child,
                        $taxRateRequest,
                        $itemTaxGroups,
                        $catalogPriceInclTax
                    );
                }
                $this->_recalculateParent($item);
            } else {
                $this->_rowBaseProcessItemTax(
                    $address,
                    $item,
                    $taxRateRequest,
                    $itemTaxGroups,
                    $catalogPriceInclTax
                );
            }
        }

        if ($address->getQuote()->getTaxesForItems()) {
            $itemTaxGroups += $address->getQuote()->getTaxesForItems();
        }
        $address->getQuote()->setTaxesForItems($itemTaxGroups);
        return $this;
    }

    /**
     * Calculate item tax amount based on row total
     *
     * @param   AbstractItem $item
     * @param   float $rate
     * @param   array $taxGroups
     * @param   string $taxId
     * @param   bool $recalculateRowTotalInclTax
     * @return  $this
     */
    protected function _calcRowTaxAmount(
        AbstractItem $item,
        $rate,
        &$taxGroups = null,
        $taxId = null,
        $recalculateRowTotalInclTax = false
    ) {
        $inclTax = $item->getIsPriceInclTax();
        $subtotal = $taxSubtotal = $item->getTaxableAmount();
        $baseSubtotal = $baseTaxSubtotal = $item->getBaseTaxableAmount();
        $extraRowTaxableAmount = $item->getExtraRowTaxableAmount();
        $baseExtraRowTaxableAmount = $item->getBaseExtraRowTaxableAmount();
        $rateKey = ($taxId == null) ? (string)$rate : $taxId;

        $hiddenTax = 0;
        $baseHiddenTax = 0;
        $rowTaxBeforeDiscount = 0;
        $baseRowTaxBeforeDiscount = 0;

        switch ($this->_taxData->getCalculationSequence($this->_store)) {
            case Calculation::CALC_TAX_BEFORE_DISCOUNT_ON_EXCL:
            case Calculation::CALC_TAX_BEFORE_DISCOUNT_ON_INCL:
                $rowTaxBeforeDiscount = $this->_calculator->calcTaxAmount($subtotal, $rate, $inclTax, false);
                $baseRowTaxBeforeDiscount = $this->_calculator->calcTaxAmount($baseSubtotal, $rate, $inclTax, false);

                $rowTaxBeforeDiscount = $rowTax = $this->_calculator->round($rowTaxBeforeDiscount);
                $baseRowTaxBeforeDiscount = $baseRowTax = $this->_calculator->round($baseRowTaxBeforeDiscount);
                break;
            case Calculation::CALC_TAX_AFTER_DISCOUNT_ON_EXCL:
            case Calculation::CALC_TAX_AFTER_DISCOUNT_ON_INCL:
                $discountAmount = $item->getDiscountAmount();
                $baseDiscountAmount = $item->getBaseDiscountAmount();
                $rowTax = $this->_calculator->calcTaxAmount(max($subtotal - $discountAmount, 0), $rate, $inclTax);
                $baseRowTax = $this->_calculator->calcTaxAmount(
                    max($baseSubtotal - $baseDiscountAmount, 0),
                    $rate,
                    $inclTax
                );
                $rowTax = $this->_calculator->round($rowTax);
                $baseRowTax = $this->_calculator->round($baseRowTax);

                //Calculate the Row Tax before discount
                $rowTaxBeforeDiscount = $this->_calculator->calcTaxAmount(
                    $subtotal,
                    $rate,
                    $inclTax,
                    false
                );
                $baseRowTaxBeforeDiscount = $this->_calculator->calcTaxAmount(
                    $baseSubtotal,
                    $rate,
                    $inclTax,
                    false
                );

                $rowTaxBeforeDiscount = max(0, $this->_calculator->round($rowTaxBeforeDiscount));
                $baseRowTaxBeforeDiscount = max(0, $this->_calculator->round($baseRowTaxBeforeDiscount));

                if ($inclTax && $discountAmount > 0) {
                    $hiddenTax = $rowTaxBeforeDiscount - $rowTax;
                    $baseHiddenTax = $baseRowTaxBeforeDiscount - $baseRowTax;
                    $this->_hiddenTaxes[] = array(
                        'rate_key' => $rateKey,
                        'qty' => 1,
                        'item' => $item,
                        'value' => $hiddenTax,
                        'base_value' => $baseHiddenTax,
                        'incl_tax' => $inclTax
                    );
                } elseif ($discountAmount > $subtotal) {
                    // case with 100% discount on price incl. tax
                    $hiddenTax = $discountAmount - $subtotal;
                    $baseHiddenTax = $baseDiscountAmount - $baseSubtotal;
                    $this->_hiddenTaxes[] = array(
                        'rate_key' => $rateKey,
                        'qty' => 1,
                        'item' => $item,
                        'value' => $hiddenTax,
                        'base_value' => $baseHiddenTax,
                        'incl_tax' => $inclTax
                    );
                }
                break;
        }
        //round tax on extra taxable separately
        $rowExtraTaxAmount = $this->_calculator->calcTaxAmount($extraRowTaxableAmount, $rate, $inclTax, true);
        $baseRowExtraTaxAmount = $this->_calculator->calcTaxAmount($baseExtraRowTaxableAmount, $rate, $inclTax, true);
        $rowTax += $rowExtraTaxAmount;
        $baseRowTax += $baseRowExtraTaxAmount;

        $item->setTaxAmount($item->getTaxAmount() + max(0, $rowTax));
        $item->setBaseTaxAmount($item->getBaseTaxAmount() + max(0, $baseRowTax));
        if (is_array($taxGroups)) {
            $taxGroups[$rateKey]['tax'] = max(0, $rowTax);
            $taxGroups[$rateKey]['base_tax'] = max(0, $baseRowTax);
        }

        $rowTotalInclTax = $item->getRowTotalInclTax();
        if (!isset($rowTotalInclTax) || $recalculateRowTotalInclTax) {
            if ($this->_config->priceIncludesTax($this->_store)) {
                $item->setRowTotalInclTax($subtotal);
                $item->setBaseRowTotalInclTax($baseSubtotal);
            } else {
                $item->setRowTotalInclTax($item->getRowTotalInclTax() + $rowTaxBeforeDiscount);
                $item->setBaseRowTotalInclTax($item->getBaseRowTotalInclTax() + $baseRowTaxBeforeDiscount);
            }
        }
        return $this;
    }

    /**
     * Calculate address total tax based on address subtotal
     *
     * @param   Address $address
     * @param   \Magento\Framework\Object $taxRateRequest
     * @return  $this
     */
    protected function _totalBaseCalculation(
        Address $address,
        \Magento\Framework\Object $taxRateRequest
    ) {
        $items = $this->_getAddressItems($address);
        $store = $address->getQuote()->getStore();
        $taxGroups = array();
        $itemTaxGroups = array();
        $catalogPriceInclTax = $this->_config->priceIncludesTax($store);

        foreach ($items as $item) {
            if ($item->getParentItem()) {
                continue;
            }

            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                foreach ($item->getChildren() as $child) {
                    $this->_totalBaseProcessItemTax(
                        $child,
                        $taxRateRequest,
                        $taxGroups,
                        $itemTaxGroups,
                        $catalogPriceInclTax
                    );
                }
                $this->_recalculateParent($item);
            } else {
                $this->_totalBaseProcessItemTax(
                    $item,
                    $taxRateRequest,
                    $taxGroups,
                    $itemTaxGroups,
                    $catalogPriceInclTax
                );
            }
        }

        if ($address->getQuote()->getTaxesForItems()) {
            $itemTaxGroups += $address->getQuote()->getTaxesForItems();
        }
        $address->getQuote()->setTaxesForItems($itemTaxGroups);

        foreach ($taxGroups as $taxId => $data) {
            if ($catalogPriceInclTax) {
                $rate = (float)$taxId;
            } else {
                $rate = $data['applied_rates'][0]['percent'];
            }

            $totalTax = array_sum($data['tax']);
            $baseTotalTax = array_sum($data['base_tax']);
            $this->_addAmount($totalTax);
            $this->_addBaseAmount($baseTotalTax);
            $totalTaxRounded = $this->_calculator->round($totalTax);
            $baseTotalTaxRounded = $this->_calculator->round($totalTaxRounded);
            $this->_saveAppliedTaxes($address, $data['applied_rates'], $totalTaxRounded, $baseTotalTaxRounded, $rate);
        }

        return $this;
    }

    /**
     *
     * @param AbstractItem $item
     * @param \Magento\Framework\Object $taxRateRequest
     * @param array $taxGroups
     * @param array $itemTaxGroups
     * @param bool $catalogPriceInclTax
     * @return $this
     */
    protected function _totalBaseProcessItemTax(
        AbstractItem $item,
        \Magento\Framework\Object $taxRateRequest,
        &$taxGroups,
        &$itemTaxGroups,
        $catalogPriceInclTax
    ) {
        $taxRateRequest->setProductClassId($item->getProduct()->getTaxClassId());
        $appliedRates = $this->_calculator->getAppliedRates($taxRateRequest);
        $rate = $this->_calculator->getRate($taxRateRequest);

        $recalculateRowTotalInclTax = $this->initializeItemTax($item, $appliedRates, $rate);

        if ($catalogPriceInclTax) {
            $taxGroups[(string)$rate]['applied_rates'] = $appliedRates;
            $taxGroups[(string)$rate]['incl_tax'] = $item->getIsPriceInclTax();
            $this->_aggregateTaxPerRate($item, $rate, $taxGroups);
        } else {
            //need to calculate each tax separately
            foreach ($appliedRates as $appliedTax) {
                $taxId = $appliedTax['id'];
                $taxRate = $appliedTax['percent'];
                $taxGroups[$taxId]['applied_rates'] = array($appliedTax);
                $taxGroups[$taxId]['incl_tax'] = $item->getIsPriceInclTax();
                $this->_aggregateTaxPerRate($item, $taxRate, $taxGroups, $taxId, $recalculateRowTotalInclTax);
            }
        }
        if ($rate > 0) {
            $itemTaxGroups[$item->getId()] = $appliedRates;
        }
        return $this;
    }

    /**
     * Aggregate row totals per tax rate in array
     *
     * @param   AbstractItem $item
     * @param   float $rate
     * @param   array &$taxGroups
     * @param   string $taxId
     * @param   bool $recalculateRowTotalInclTax
     * @return  $this
     */
    protected function _aggregateTaxPerRate(
        AbstractItem $item,
        $rate,
        &$taxGroups,
        $taxId = null,
        $recalculateRowTotalInclTax = false
    ) {
        $inclTax = $item->getIsPriceInclTax();
        $rateKey = ($taxId == null) ? (string)$rate : $taxId;
        $taxSubtotal = $subtotal = $item->getTaxableAmount();
        $baseTaxSubtotal = $baseSubtotal = $item->getBaseTaxableAmount();
        $extraTaxableAmount = $item->getExtraRowTaxableAmount();
        $baseExtraTaxableAmount =  $item->getBaseExtraRowTaxableAmount();

        if (!isset($taxGroups[$rateKey]['totals'])) {
            $taxGroups[$rateKey]['totals'] = array();
            $taxGroups[$rateKey]['base_totals'] = array();
        }

        $hiddenTax = null;
        $baseHiddenTax = null;
        $discount = 0;
        $rowTaxBeforeDiscount = 0;
        $baseRowTaxBeforeDiscount = 0;
        switch ($this->_taxData->getCalculationSequence($this->_store)) {
            case Calculation::CALC_TAX_BEFORE_DISCOUNT_ON_EXCL:
            case Calculation::CALC_TAX_BEFORE_DISCOUNT_ON_INCL:
                $rowTaxBeforeDiscount = $this->_calculator->calcTaxAmount($subtotal, $rate, $inclTax, false);
                $baseRowTaxBeforeDiscount = $this->_calculator->calcTaxAmount($baseSubtotal, $rate, $inclTax, false);

                $taxBeforeDiscountRounded = $rowTax = $this->_deltaRound($rowTaxBeforeDiscount, $rateKey, $inclTax);
                $baseTaxBeforeDiscountRounded = $baseRowTax = $this->_deltaRound(
                    $baseRowTaxBeforeDiscount,
                    $rateKey,
                    $inclTax,
                    'base'
                );

                //Round extra tax amount separately
                $extraTaxAmount = $this->_calculator->calcTaxAmount($extraTaxableAmount, $rate, $inclTax, true);
                $baseExtraTaxAmount = $this->_calculator->calcTaxAmount($baseExtraTaxableAmount, $rate, $inclTax, true);
                $rowTax += $extraTaxAmount;
                $baseRowTax += $baseExtraTaxAmount;

                $item->setTaxAmount($item->getTaxAmount() + max(0, $rowTax));
                $item->setBaseTaxAmount($item->getBaseTaxAmount() + max(0, $baseRowTax));
                break;
            case Calculation::CALC_TAX_AFTER_DISCOUNT_ON_EXCL:
            case Calculation::CALC_TAX_AFTER_DISCOUNT_ON_INCL:
                if ($this->_taxData->applyTaxOnOriginalPrice($this->_store)) {
                    $discount = $item->getOriginalDiscountAmount();
                    $baseDiscount = $item->getBaseOriginalDiscountAmount();
                } else {
                    $discount = $item->getDiscountAmount();
                    $baseDiscount = $item->getBaseDiscountAmount();
                }

                $taxSubtotal = max($subtotal - $discount, 0);
                $baseTaxSubtotal = max($baseSubtotal - $baseDiscount, 0);

                $rowTax = $this->_calculator->calcTaxAmount($taxSubtotal, $rate, $inclTax, false);
                $baseRowTax = $this->_calculator->calcTaxAmount($baseTaxSubtotal, $rate, $inclTax, false);

                $rowTax = $this->_deltaRound($rowTax, $rateKey, $inclTax);
                $baseRowTax = $this->_deltaRound($baseRowTax, $rateKey, $inclTax, 'base');

                //Calculate the Row taxes before discount
                $rowTaxBeforeDiscount = $this->_calculator->calcTaxAmount(
                    $subtotal,
                    $rate,
                    $inclTax,
                    false
                );
                $baseRowTaxBeforeDiscount = $this->_calculator->calcTaxAmount(
                    $baseSubtotal,
                    $rate,
                    $inclTax,
                    false
                );

                $taxBeforeDiscountRounded = max(
                    0,
                    $this->_deltaRound($rowTaxBeforeDiscount, $rateKey, $inclTax, 'tax_before_discount')
                );
                $baseTaxBeforeDiscountRounded = max(
                    0,
                    $this->_deltaRound($baseRowTaxBeforeDiscount, $rateKey, $inclTax, 'tax_before_discount_base')
                );


                if ($inclTax && $discount > 0) {
                    $hiddenTax = $taxBeforeDiscountRounded - max(0, $rowTax);
                    $baseHiddenTax = $baseTaxBeforeDiscountRounded - max(0, $baseRowTax);
                    $this->_hiddenTaxes[] = array(
                        'rate_key' => $rateKey,
                        'qty' => 1,
                        'item' => $item,
                        'value' => $hiddenTax,
                        'base_value' => $baseHiddenTax,
                        'incl_tax' => $inclTax
                    );
                }

                //Round extra tax amount separately
                $extraTaxAmount = $this->_calculator->calcTaxAmount($extraTaxableAmount, $rate, $inclTax, true);
                $baseExtraTaxAmount = $this->_calculator->calcTaxAmount($baseExtraTaxableAmount, $rate, $inclTax, true);
                $rowTax += $extraTaxAmount;
                $baseRowTax += $baseExtraTaxAmount;

                $item->setTaxAmount($item->getTaxAmount() + max(0, $rowTax));
                $item->setBaseTaxAmount($item->getBaseTaxAmount() + max(0, $baseRowTax));

                break;
        }

        $rowTotalInclTax = $item->getRowTotalInclTax();
        if (!isset($rowTotalInclTax) || $recalculateRowTotalInclTax) {
            if ($this->_config->priceIncludesTax($this->_store)) {
                $item->setRowTotalInclTax($subtotal);
                $item->setBaseRowTotalInclTax($baseSubtotal);
            } else {
                $item->setRowTotalInclTax($item->getRowTotalInclTax() + $taxBeforeDiscountRounded);
                $item->setBaseRowTotalInclTax($item->getBaseRowTotalInclTax() + $baseTaxBeforeDiscountRounded);
            }
        }

        $taxGroups[$rateKey]['totals'][] = max(0, $taxSubtotal);
        $taxGroups[$rateKey]['base_totals'][] = max(0, $baseTaxSubtotal);
        $taxGroups[$rateKey]['tax'][] = max(0, $rowTax);
        $taxGroups[$rateKey]['base_tax'][] = max(0, $baseRowTax);

        return $this;
    }

    /**
     * Round price based on previous rounding operation delta
     *
     * @param float $price
     * @param string $rate
     * @param bool $direction price including or excluding tax
     * @param string $type
     * @return float
     */
    protected function _deltaRound($price, $rate, $direction, $type = 'regular')
    {
        if ($price) {
            $rate = (string)$rate;
            $type = $type . $direction;
            // initialize the delta to a small number to avoid non-deterministic behavior with rounding of 0.5
            $delta = isset($this->_roundingDeltas[$type][$rate]) ? $this->_roundingDeltas[$type][$rate] : 0.000001;
            $price += $delta;
            $this->_roundingDeltas[$type][$rate] = $price - $this->_calculator->round($price);
            $price = $this->_calculator->round($price);
        }
        return $price;
    }

    /**
     * Recalculate parent item amounts base on children data
     *
     * @param   AbstractItem $item
     * @return  $this
     */
    protected function _recalculateParent(AbstractItem $item)
    {
        $rowTaxAmount = 0;
        $baseRowTaxAmount = 0;
        foreach ($item->getChildren() as $child) {
            $rowTaxAmount += $child->getTaxAmount();
            $baseRowTaxAmount += $child->getBaseTaxAmount();
        }
        $item->setTaxAmount($rowTaxAmount);
        $item->setBaseTaxAmount($baseRowTaxAmount);
        return $this;
    }

    /**
     * Collect applied tax rates information on address level
     *
     * @param Address $address
     * @param array $applied
     * @param float $amount
     * @param float $baseAmount
     * @param float $rate
     * @return void
     */
    protected function _saveAppliedTaxes(
        Address $address,
        $applied,
        $amount,
        $baseAmount,
        $rate
    ) {
        $previouslyAppliedTaxes = $address->getAppliedTaxes();
        $process = count($previouslyAppliedTaxes);

        foreach ($applied as $row) {
            if ($row['percent'] == 0) {
                continue;
            }
            if (!isset($previouslyAppliedTaxes[$row['id']])) {
                $row['process'] = $process;
                $row['amount'] = 0;
                $row['base_amount'] = 0;
                $previouslyAppliedTaxes[$row['id']] = $row;
            }

            if (!is_null($row['percent'])) {
                $row['percent'] = $row['percent'] ? $row['percent'] : 1;
                $rate = $rate ? $rate : 1;

                $appliedAmount = $amount / $rate * $row['percent'];
                $baseAppliedAmount = $baseAmount / $rate * $row['percent'];
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

    /**
     * Add tax totals information to address object
     *
     * @param   Address $address
     * @return  $this
     */
    public function fetch(Address $address)
    {
        $applied = $address->getAppliedTaxes();
        $store = $address->getQuote()->getStore();
        $amount = $address->getTaxAmount();

        $items = $this->_getAddressItems($address);
        $discountTaxCompensation = 0;
        foreach ($items as $item) {
            $discountTaxCompensation += $item->getDiscountTaxCompensation();
        }
        $taxAmount = $amount + $discountTaxCompensation;

        $area = null;
        if ($this->_config->displayCartTaxWithGrandTotal($store) && $address->getGrandTotal()) {
            $area = 'taxes';
        }

        if ($amount != 0 || $this->_config->displayCartZeroTax($store)) {
            $address->addTotal(
                array(
                    'code' => $this->getCode(),
                    'title' => __('Tax'),
                    'full_info' => $applied ? $applied : array(),
                    'value' => $amount,
                    'area' => $area
                )
            );
        }

        $store = $address->getQuote()->getStore();
        /**
         * Modify subtotal
         */
        if ($this->_config->displayCartSubtotalBoth($store) || $this->_config->displayCartSubtotalInclTax($store)) {
            if ($address->getSubtotalInclTax() > 0) {
                $subtotalInclTax = $address->getSubtotalInclTax();
            } else {
                $subtotalInclTax = $address->getSubtotal() + $taxAmount - $address->getShippingTaxAmount();
            }

            $address->addTotal(
                array(
                    'code' => 'subtotal',
                    'title' => __('Subtotal'),
                    'value' => $subtotalInclTax,
                    'value_incl_tax' => $subtotalInclTax,
                    'value_excl_tax' => $address->getSubtotal()
                )
            );
        }

        return $this;
    }

    /**
     * Process model configuration array.
     * This method can be used for changing totals collect sort order
     *
     * @param   array $config
     * @param   store $store
     * @return  array
     */
    public function processConfigArray($config, $store)
    {
        $calculationSequence = $this->_taxData->getCalculationSequence($store);
        switch ($calculationSequence) {
            case Calculation::CALC_TAX_BEFORE_DISCOUNT_ON_INCL:
                $config['before'][] = 'discount';
                break;
            default:
                $config['after'][] = 'discount';
                break;
        }
        return $config;
    }

    /**
     * Get Tax label
     *
     * @return string
     */
    public function getLabel()
    {
        return __('Tax');
    }
}
