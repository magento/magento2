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
 * @category    Magento
 * @package     Magento_Tax
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Tax totals calculation model
 */
namespace Magento\Tax\Model\Sales\Total\Quote;

class Tax extends \Magento\Sales\Model\Quote\Address\Total\AbstractTotal
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


    protected $_roundingDeltas = array();
    protected $_baseRoundingDeltas = array();

    protected $_store;

    /**
     * Hidden taxes array
     *
     * @var array
     */
    protected $_hiddenTaxes = array();

    /**
     * Class constructor
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
     * @param   \Magento\Sales\Model\Quote\Address $address
     * @return  \Magento\Tax\Model\Sales\Total\Quote\Tax
     */
    public function collect(\Magento\Sales\Model\Quote\Address $address)
    {
        parent::collect($address);
        $this->_roundingDeltas      = array();
        $this->_baseRoundingDeltas  = array();
        $this->_hiddenTaxes         = array();
        $address->setShippingTaxAmount(0);
        $address->setBaseShippingTaxAmount(0);

        $this->_store = $address->getQuote()->getStore();
        $customer = $address->getQuote()->getCustomer();
        if ($customer) {
            $this->_calculator->setCustomer($customer);
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
            $this->_areTaxRequestsSimilar = $this->_calculator->compareRequests(
                $this->_calculator->getRateOriginRequest($this->_store),
                $request
            );
        }

        switch ($this->_config->getAlgorithm($this->_store)) {
            case \Magento\Tax\Model\Calculation::CALC_UNIT_BASE:
                $this->_unitBaseCalculation($address, $request);
                break;
            case \Magento\Tax\Model\Calculation::CALC_ROW_BASE:
                $this->_rowBaseCalculation($address, $request);
                break;
            case \Magento\Tax\Model\Calculation::CALC_TOTAL_BASE:
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
                $item           = $taxInfoItem['item'];
                $rateKey        = $taxInfoItem['rate_key'];
                $hiddenTax      = $taxInfoItem['value'];
                $baseHiddenTax  = $taxInfoItem['base_value'];
                $inclTax        = $taxInfoItem['incl_tax'];
                $qty            = $taxInfoItem['qty'];

                if ($this->_config->getAlgorithm($this->_store) == \Magento\Tax\Model\Calculation::CALC_TOTAL_BASE) {
                    $hiddenTax      = $this->_deltaRound($hiddenTax, $rateKey, $inclTax);
                    $baseHiddenTax  = $this->_deltaRound($baseHiddenTax, $rateKey, $inclTax, 'base');
                } else {
                    $hiddenTax      = $this->_calculator->round($hiddenTax);
                    $baseHiddenTax  = $this->_calculator->round($baseHiddenTax);
                }

                $item->setHiddenTaxAmount(max(0, $qty * $hiddenTax));
                $item->setBaseHiddenTaxAmount(max(0, $qty * $baseHiddenTax));
                $this->_getAddress()->addTotalAmount('hidden_tax', $item->getHiddenTaxAmount());
                $this->_getAddress()->addBaseTotalAmount('hidden_tax', $item->getBaseHiddenTaxAmount());
            } else {
                // Shipping hidden taxes
                $rateKey        = $taxInfoItem['rate_key'];
                $hiddenTax      = $taxInfoItem['value'];
                $baseHiddenTax  = $taxInfoItem['base_value'];
                $inclTax        = $taxInfoItem['incl_tax'];

                $hiddenTax      = $this->_deltaRound($hiddenTax, $rateKey, $inclTax);
                $baseHiddenTax  = $this->_deltaRound($baseHiddenTax, $rateKey, $inclTax, 'base');

                $this->_getAddress()->setShippingHiddenTaxAmount(max(0, $hiddenTax));
                $this->_getAddress()->setBaseShippingHiddenTaxAmnt(max(0, $baseHiddenTax));
                $this->_getAddress()->addTotalAmount('shipping_hidden_tax', $hiddenTax);
                $this->_getAddress()->addBaseTotalAmount('shipping_hidden_tax', $baseHiddenTax);
            }
        }
    }

    /**
     * Tax caclulation for shipping price
     *
     * @param   \Magento\Sales\Model\Quote\Address $address
     * @param   \Magento\Object $taxRateRequest
     * @return  \Magento\Tax\Model\Sales\Total\Quote\Tax
     */
    protected function _calculateShippingTax(\Magento\Sales\Model\Quote\Address $address, $taxRateRequest)
    {
        $taxRateRequest->setProductClassId($this->_config->getShippingTaxClass($this->_store));
        $rate           = $this->_calculator->getRate($taxRateRequest);
        $inclTax        = $address->getIsShippingInclTax();
        $shipping       = $address->getShippingTaxable();
        $baseShipping   = $address->getBaseShippingTaxable();
        $rateKey        = (string)$rate;

        $hiddenTax      = null;
        $baseHiddenTax  = null;
        switch ($this->_taxData->getCalculationSequence($this->_store)) {
            case \Magento\Tax\Model\Calculation::CALC_TAX_BEFORE_DISCOUNT_ON_EXCL:
            case \Magento\Tax\Model\Calculation::CALC_TAX_BEFORE_DISCOUNT_ON_INCL:
                $tax        = $this->_calculator->calcTaxAmount($shipping, $rate, $inclTax, false);
                $baseTax    = $this->_calculator->calcTaxAmount($baseShipping, $rate, $inclTax, false);
                break;
            case \Magento\Tax\Model\Calculation::CALC_TAX_AFTER_DISCOUNT_ON_EXCL:
            case \Magento\Tax\Model\Calculation::CALC_TAX_AFTER_DISCOUNT_ON_INCL:
                $discountAmount     = $address->getShippingDiscountAmount();
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

        if ($this->_config->getAlgorithm($this->_store) == \Magento\Tax\Model\Calculation::CALC_TOTAL_BASE) {
            $tax        = $this->_deltaRound($tax, $rate, $inclTax);
            $baseTax    = $this->_deltaRound($baseTax, $rate, $inclTax, 'base');
        } else {
            $tax        = $this->_calculator->round($tax);
            $baseTax    = $this->_calculator->round($baseTax);
        }

        if ($inclTax && !empty($discountAmount)) {
            $hiddenTax      = $this->_calculator->calcTaxAmount($discountAmount, $rate, $inclTax, false);
            $baseHiddenTax  = $this->_calculator->calcTaxAmount($baseDiscountAmount, $rate, $inclTax, false);
            $this->_hiddenTaxes[] = array(
                'rate_key'   => $rateKey,
                'value'      => $hiddenTax,
                'base_value' => $baseHiddenTax,
                'incl_tax'   => $inclTax,
            );
        }

        $this->_addAmount(max(0, $tax));
        $this->_addBaseAmount(max(0, $baseTax));
        $address->setShippingTaxAmount(max(0, $tax));
        $address->setBaseShippingTaxAmount(max(0, $baseTax));
        $applied = $this->_calculator->getAppliedRates($taxRateRequest);
        $this->_saveAppliedTaxes($address, $applied, $tax, $baseTax, $rate);

        return $this;
    }

    /**
     * Calculate address tax amount based on one unit price and tax amount
     *
     * @param   \Magento\Sales\Model\Quote\Address $address
     * @return  \Magento\Tax\Model\Sales\Total\Quote\Tax
     */
    protected function _unitBaseCalculation(\Magento\Sales\Model\Quote\Address $address, $taxRateRequest)
    {
        $items = $this->_getAddressItems($address);
        $itemTaxGroups  = array();
        foreach ($items as $item) {
            if ($item->getParentItem()) {
                continue;
            }

            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                foreach ($item->getChildren() as $child) {
                    $taxRateRequest->setProductClassId($child->getProduct()->getTaxClassId());
                    $rate = $this->_calculator->getRate($taxRateRequest);
                    $this->_calcUnitTaxAmount($child, $rate);
                    $this->_addAmount($child->getTaxAmount());
                    $this->_addBaseAmount($child->getBaseTaxAmount());
                    $applied = $this->_calculator->getAppliedRates($taxRateRequest);
                    if ($rate > 0) {
                        $itemTaxGroups[$child->getId()] = $applied;
                    }
                    $this->_saveAppliedTaxes(
                        $address,
                        $applied,
                        $child->getTaxAmount(),
                        $child->getBaseTaxAmount(),
                        $rate
                    );
                    $child->setTaxRates($applied);
                }
                $this->_recalculateParent($item);
            }
            else {
                $taxRateRequest->setProductClassId($item->getProduct()->getTaxClassId());
                $rate = $this->_calculator->getRate($taxRateRequest);
                $this->_calcUnitTaxAmount($item, $rate);
                $this->_addAmount($item->getTaxAmount());
                $this->_addBaseAmount($item->getBaseTaxAmount());
                $applied = $this->_calculator->getAppliedRates($taxRateRequest);
                if ($rate > 0) {
                    $itemTaxGroups[$item->getId()] = $applied;
                }
                $this->_saveAppliedTaxes(
                    $address,
                    $applied,
                    $item->getTaxAmount(),
                    $item->getBaseTaxAmount(),
                    $rate
                );
                $item->setTaxRates($applied);
            }
        }
        if ($address->getQuote()->getTaxesForItems()) {
            $itemTaxGroups += $address->getQuote()->getTaxesForItems();
        }
        $address->getQuote()->setTaxesForItems($itemTaxGroups);
        return $this;
    }

    /**
     * Calculate unit tax anount based on unit price
     *
     * @param   \Magento\Sales\Model\Quote\Item\AbstractItem $item
     * @param   float $rate
     * @return  \Magento\Tax\Model\Sales\Total\Quote\Tax
     */
    protected function _calcUnitTaxAmount(\Magento\Sales\Model\Quote\Item\AbstractItem $item, $rate)
    {
        $qty        = $item->getTotalQty();
        $inclTax    = $item->getIsPriceInclTax();
        $price      = $item->getTaxableAmount() + $item->getExtraTaxableAmount();
        $basePrice  = $item->getBaseTaxableAmount() + $item->getBaseExtraTaxableAmount();
        $rateKey    = (string)$rate;
        $item->setTaxPercent($rate);

        $hiddenTax      = null;
        $baseHiddenTax  = null;
        switch ($this->_config->getCalculationSequence($this->_store)) {
            case \Magento\Tax\Model\Calculation::CALC_TAX_BEFORE_DISCOUNT_ON_EXCL:
            case \Magento\Tax\Model\Calculation::CALC_TAX_BEFORE_DISCOUNT_ON_INCL:
                $unitTax        = $this->_calculator->calcTaxAmount($price, $rate, $inclTax);
                $baseUnitTax    = $this->_calculator->calcTaxAmount($basePrice, $rate, $inclTax);
                break;
            case \Magento\Tax\Model\Calculation::CALC_TAX_AFTER_DISCOUNT_ON_EXCL:
            case \Magento\Tax\Model\Calculation::CALC_TAX_AFTER_DISCOUNT_ON_INCL:
                $discountAmount     = $item->getDiscountAmount() / $qty;
                $baseDiscountAmount = $item->getBaseDiscountAmount() / $qty;

                $unitTax = $this->_calculator->calcTaxAmount($price, $rate, $inclTax);
                $discountRate = ($unitTax/$price) * 100;
                $unitTaxDiscount = $this->_calculator->calcTaxAmount($discountAmount, $discountRate, $inclTax, false);
                $unitTax = max($unitTax - $unitTaxDiscount, 0);
                $baseUnitTax = $this->_calculator->calcTaxAmount($basePrice, $rate, $inclTax);
                $baseDiscountRate = ($baseUnitTax/$basePrice) * 100;
                $baseUnitTaxDiscount = $this->_calculator
                    ->calcTaxAmount($baseDiscountAmount, $baseDiscountRate, $inclTax, false);
                $baseUnitTax = max($baseUnitTax - $baseUnitTaxDiscount, 0);

                if ($inclTax && $discountAmount > 0) {
                    $hiddenTax      = $this->_calculator->calcTaxAmount($discountAmount, $rate, $inclTax, false);
                    $baseHiddenTax  = $this->_calculator->calcTaxAmount($baseDiscountAmount, $rate, $inclTax, false);
                    $this->_hiddenTaxes[] = array(
                        'rate_key'   => $rateKey,
                        'qty'        => $qty,
                        'item'       => $item,
                        'value'      => $hiddenTax,
                        'base_value' => $baseHiddenTax,
                        'incl_tax'   => $inclTax,
                    );
                } elseif ($discountAmount > $price) { // case with 100% discount on price incl. tax
                    $hiddenTax      = $discountAmount - $price;
                    $baseHiddenTax  = $baseDiscountAmount - $basePrice;
                    $this->_hiddenTaxes[] = array(
                        'rate_key'   => $rateKey,
                        'qty'        => $qty,
                        'item'       => $item,
                        'value'      => $hiddenTax,
                        'base_value' => $baseHiddenTax,
                        'incl_tax'   => $inclTax,
                    );
                }
                break;
        }
        $item->setTaxAmount($this->_store->roundPrice(max(0, $qty*$unitTax)));
        $item->setBaseTaxAmount($this->_store->roundPrice(max(0, $qty*$baseUnitTax)));

        return $this;
    }

    /**
     * Calculate address total tax based on row total
     *
     * @param   \Magento\Sales\Model\Quote\Address $address
     * @param   \Magento\Object $taxRateRequest
     * @return  \Magento\Tax\Model\Sales\Total\Quote\Tax
     */
    protected function _rowBaseCalculation(\Magento\Sales\Model\Quote\Address $address, $taxRateRequest)
    {
        $items = $this->_getAddressItems($address);
        $itemTaxGroups  = array();
        foreach ($items as $item) {
            if ($item->getParentItem()) {
                continue;
            }
            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                foreach ($item->getChildren() as $child) {
                    $taxRateRequest->setProductClassId($child->getProduct()->getTaxClassId());
                    $rate = $this->_calculator->getRate($taxRateRequest);
                    $this->_calcRowTaxAmount($child, $rate);
                    $this->_addAmount($child->getTaxAmount());
                    $this->_addBaseAmount($child->getBaseTaxAmount());
                    $applied = $this->_calculator->getAppliedRates($taxRateRequest);
                    if ($rate > 0) {
                        $itemTaxGroups[$child->getId()] = $applied;
                    }
                    $this->_saveAppliedTaxes(
                        $address,
                        $applied,
                        $child->getTaxAmount(),
                        $child->getBaseTaxAmount(),
                        $rate
                    );
                    $child->setTaxRates($applied);
                }
                $this->_recalculateParent($item);
            }
            else {
                $taxRateRequest->setProductClassId($item->getProduct()->getTaxClassId());
                $rate = $this->_calculator->getRate($taxRateRequest);
                $this->_calcRowTaxAmount($item, $rate);
                $this->_addAmount($item->getTaxAmount());
                $this->_addBaseAmount($item->getBaseTaxAmount());
                $applied = $this->_calculator->getAppliedRates($taxRateRequest);
                if ($rate > 0) {
                    $itemTaxGroups[$item->getId()] = $applied;
                }
                $this->_saveAppliedTaxes(
                    $address,
                    $applied,
                    $item->getTaxAmount(),
                    $item->getBaseTaxAmount(),
                    $rate
                );
                $item->setTaxRates($applied);
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
     * @param   \Magento\Sales\Model\Quote\Item\AbstractItem $item
     * @param   float $rate
     * @return  \Magento\Tax\Model\Sales\Total\Quote\Tax
     */
    protected function _calcRowTaxAmount($item, $rate)
    {
        $inclTax        = $item->getIsPriceInclTax();
        $subtotal       = $item->getTaxableAmount() + $item->getExtraRowTaxableAmount();
        $baseSubtotal   = $item->getBaseTaxableAmount() + $item->getBaseExtraRowTaxableAmount();
        $rateKey        = (string)$rate;
        $item->setTaxPercent($rate);

        $hiddenTax      = null;
        $baseHiddenTax  = null;
        switch ($this->_taxData->getCalculationSequence($this->_store)) {
            case \Magento\Tax\Model\Calculation::CALC_TAX_BEFORE_DISCOUNT_ON_EXCL:
            case \Magento\Tax\Model\Calculation::CALC_TAX_BEFORE_DISCOUNT_ON_INCL:
                $rowTax     = $this->_calculator->calcTaxAmount($subtotal, $rate, $inclTax);
                $baseRowTax = $this->_calculator->calcTaxAmount($baseSubtotal, $rate, $inclTax);
                break;
            case \Magento\Tax\Model\Calculation::CALC_TAX_AFTER_DISCOUNT_ON_EXCL:
            case \Magento\Tax\Model\Calculation::CALC_TAX_AFTER_DISCOUNT_ON_INCL:
                $discountAmount     = $item->getDiscountAmount();
                $baseDiscountAmount = $item->getBaseDiscountAmount();
                $rowTax = $this->_calculator->calcTaxAmount(
                    max($subtotal - $discountAmount, 0),
                    $rate,
                    $inclTax
                );
                $baseRowTax = $this->_calculator->calcTaxAmount(
                    max($baseSubtotal - $baseDiscountAmount, 0),
                    $rate,
                    $inclTax
                );
                if ($inclTax && $discountAmount > 0) {
                    $hiddenTax      = $this->_calculator->calcTaxAmount($discountAmount, $rate, $inclTax, false);
                    $baseHiddenTax  = $this->_calculator->calcTaxAmount($baseDiscountAmount, $rate, $inclTax, false);
                    $this->_hiddenTaxes[] = array(
                        'rate_key'   => $rateKey,
                        'qty'        => 1,
                        'item'       => $item,
                        'value'      => $hiddenTax,
                        'base_value' => $baseHiddenTax,
                        'incl_tax'   => $inclTax,
                    );
                } elseif ($discountAmount > $subtotal) { // case with 100% discount on price incl. tax
                    $hiddenTax      = $discountAmount - $subtotal;
                    $baseHiddenTax  = $baseDiscountAmount - $baseSubtotal;
                    $this->_hiddenTaxes[] = array(
                        'rate_key'   => $rateKey,
                        'qty'        => 1,
                        'item'       => $item,
                        'value'      => $hiddenTax,
                        'base_value' => $baseHiddenTax,
                        'incl_tax'   => $inclTax,
                    );
                }
                break;
        }
        $item->setTaxAmount(max(0, $rowTax));
        $item->setBaseTaxAmount(max(0, $baseRowTax));
        return $this;
    }

    /**
     * Calculate address total tax based on address subtotal
     *
     * @param   \Magento\Sales\Model\Quote\Address $address
     * @param   \Magento\Object $taxRateRequest
     * @return  \Magento\Tax\Model\Sales\Total\Quote\Tax
     */
    protected function _totalBaseCalculation(\Magento\Sales\Model\Quote\Address $address, $taxRateRequest)
    {
        $items          = $this->_getAddressItems($address);
        $store          = $address->getQuote()->getStore();
        $taxGroups      = array();
        $itemTaxGroups  = array();

        foreach ($items as $item) {
            if ($item->getParentItem()) {
                continue;
            }

            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                foreach ($item->getChildren() as $child) {
                    $taxRateRequest->setProductClassId($child->getProduct()->getTaxClassId());
                    $rate = $this->_calculator->getRate($taxRateRequest);
                    $applied_rates = $this->_calculator->getAppliedRates($taxRateRequest);
                    $taxGroups[(string)$rate]['applied_rates'] = $applied_rates;
                    $taxGroups[(string)$rate]['incl_tax'] = $child->getIsPriceInclTax();
                    $this->_aggregateTaxPerRate($child, $rate, $taxGroups);
                    if ($rate > 0) {
                        $itemTaxGroups[$child->getId()] = $applied_rates;
                    }
                }
                $this->_recalculateParent($item);
            } else {
                $taxRateRequest->setProductClassId($item->getProduct()->getTaxClassId());
                $rate = $this->_calculator->getRate($taxRateRequest);
                $applied_rates = $this->_calculator->getAppliedRates($taxRateRequest);
                $taxGroups[(string)$rate]['applied_rates'] = $applied_rates;
                $taxGroups[(string)$rate]['incl_tax'] = $item->getIsPriceInclTax();
                $this->_aggregateTaxPerRate($item, $rate, $taxGroups);
                if ($rate > 0) {
                    $itemTaxGroups[$item->getId()] = $applied_rates;
                }
            }
        }

        if ($address->getQuote()->getTaxesForItems()) {
            $itemTaxGroups += $address->getQuote()->getTaxesForItems();
        }
        $address->getQuote()->setTaxesForItems($itemTaxGroups);

        foreach ($taxGroups as $rateKey => $data) {
            $rate = (float) $rateKey;
            $inclTax = $data['incl_tax'];
            $totalTax = $this->_calculator->calcTaxAmount(array_sum($data['totals']), $rate, $inclTax);
            $baseTotalTax = $this->_calculator->calcTaxAmount(array_sum($data['base_totals']), $rate, $inclTax);
            $this->_addAmount($totalTax);
            $this->_addBaseAmount($baseTotalTax);
            $this->_saveAppliedTaxes($address, $data['applied_rates'], $totalTax, $baseTotalTax, $rate);
        }
        return $this;
    }

    /**
     * Aggregate row totals per tax rate in array
     *
     * @param   \Magento\Sales\Model\Quote\Item\AbstractItem $item
     * @param   float $rate
     * @param   array $taxGroups
     * @return  \Magento\Tax\Model\Sales\Total\Quote\Tax
     */
    protected function _aggregateTaxPerRate($item, $rate, &$taxGroups)
    {
        $inclTax        = $item->getIsPriceInclTax();
        $rateKey        = (string) $rate;
        $taxSubtotal    = $subtotal     = $item->getTaxableAmount() + $item->getExtraRowTaxableAmount();
        $baseTaxSubtotal= $baseSubtotal = $item->getBaseTaxableAmount() + $item->getBaseExtraRowTaxableAmount();
        $item->setTaxPercent($rate);

        if (!isset($taxGroups[$rateKey]['totals'])) {
            $taxGroups[$rateKey]['totals'] = array();
            $taxGroups[$rateKey]['base_totals'] = array();
        }

        $hiddenTax      = null;
        $baseHiddenTax  = null;
        switch ($this->_taxData->getCalculationSequence($this->_store)) {
            case \Magento\Tax\Model\Calculation::CALC_TAX_BEFORE_DISCOUNT_ON_EXCL:
            case \Magento\Tax\Model\Calculation::CALC_TAX_BEFORE_DISCOUNT_ON_INCL:
                $rowTax             = $this->_calculator->calcTaxAmount($subtotal, $rate, $inclTax, false);
                $baseRowTax         = $this->_calculator->calcTaxAmount($baseSubtotal, $rate, $inclTax, false);
                break;
            case \Magento\Tax\Model\Calculation::CALC_TAX_AFTER_DISCOUNT_ON_EXCL:
            case \Magento\Tax\Model\Calculation::CALC_TAX_AFTER_DISCOUNT_ON_INCL:
                if ($this->_taxData->applyTaxOnOriginalPrice($this->_store)) {
                    $discount           = $item->getOriginalDiscountAmount();
                    $baseDiscount       = $item->getBaseOriginalDiscountAmount();
                } else {
                    $discount           = $item->getDiscountAmount();
                    $baseDiscount       = $item->getBaseDiscountAmount();
                }

                $taxSubtotal        = max($subtotal - $discount, 0);
                $baseTaxSubtotal    = max($baseSubtotal - $baseDiscount, 0);
                $rowTax             = $this->_calculator->calcTaxAmount($taxSubtotal, $rate, $inclTax, false);
                $baseRowTax         = $this->_calculator->calcTaxAmount($baseTaxSubtotal, $rate, $inclTax, false);
                if (!$item->getNoDiscount() && $item->getWeeeTaxApplied()) {
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
                }

                if ($inclTax && $discount > 0) {
                    $hiddenTax      = $this->_calculator->calcTaxAmount($discount, $rate, $inclTax, false);
                    $baseHiddenTax  = $this->_calculator->calcTaxAmount($baseDiscount, $rate, $inclTax, false);
                    $this->_hiddenTaxes[] = array(
                        'rate_key'   => $rateKey,
                        'qty'        => 1,
                        'item'       => $item,
                        'value'      => $hiddenTax,
                        'base_value' => $baseHiddenTax,
                        'incl_tax'   => $inclTax,
                    );
                }
                break;
        }

        $rowTax     = $this->_deltaRound($rowTax, $rateKey, $inclTax);
        $baseRowTax = $this->_deltaRound($baseRowTax, $rateKey, $inclTax, 'base');
        $item->setTaxAmount(max(0, $rowTax));
        $item->setBaseTaxAmount(max(0, $baseRowTax));

        if (isset($rowTaxBeforeDiscount) && isset($baseRowTaxBeforeDiscount)) {
            $taxBeforeDiscount = max(
                0,
                $this->_deltaRound($rowTaxBeforeDiscount, $rateKey, $inclTax)
            );
            $baseTaxBeforeDiscount = max(
                0,
                $this->_deltaRound($baseRowTaxBeforeDiscount, $rateKey, $inclTax, 'base')
            );

            $item->setDiscountTaxCompensation($taxBeforeDiscount - max(0, $rowTax));
            $item->setBaseDiscountTaxCompensation($baseTaxBeforeDiscount - max(0, $baseRowTax));
        }

        $taxGroups[$rateKey]['totals'][]        = max(0, $taxSubtotal);
        $taxGroups[$rateKey]['base_totals'][]   = max(0, $baseTaxSubtotal);
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
    protected function _deltaRound($price, $rate, $direction, $type='regular')
    {
        if ($price) {
            $rate  = (string) $rate;
            $type  = $type . $direction;
            $delta = isset($this->_roundingDeltas[$type][$rate]) ? $this->_roundingDeltas[$type][$rate] : 0;
            $price += $delta;
            $this->_roundingDeltas[$type][$rate] = $price - $this->_calculator->round($price);
            $price = $this->_calculator->round($price);
        }
        return $price;
    }

    /**
     * Recalculate parent item amounts base on children data
     *
     * @param   \Magento\Sales\Model\Quote\Item\AbstractItem $item
     * @return  \Magento\Tax\Model\Sales\Total\Quote\Tax
     */
    protected function _recalculateParent(\Magento\Sales\Model\Quote\Item\AbstractItem $item)
    {
        $rowTaxAmount       = 0;
        $baseRowTaxAmount   = 0;
        foreach ($item->getChildren() as $child) {
            $rowTaxAmount       += $child->getTaxAmount();
            $baseRowTaxAmount   += $child->getBaseTaxAmount();
        }
        $item->setTaxAmount($rowTaxAmount);
        $item->setBaseTaxAmount($baseRowTaxAmount);
        return $this;
    }

    /**
     * Collect applied tax rates information on address level
     *
     * @param   \Magento\Sales\Model\Quote\Address $address
     * @param   array $applied
     * @param   float $amount
     * @param   float $baseAmount
     * @param   float $rate
     */
    protected function _saveAppliedTaxes(\Magento\Sales\Model\Quote\Address $address, $applied, $amount, $baseAmount, $rate)
    {
        $previouslyAppliedTaxes = $address->getAppliedTaxes();
        $process = count($previouslyAppliedTaxes);

        foreach ($applied as $row) {
            if ($row['percent'] == 0) {
                continue;
            }
            if (!isset($previouslyAppliedTaxes[$row['id']])) {
                $row['process']     = $process;
                $row['amount']      = 0;
                $row['base_amount'] = 0;
                $previouslyAppliedTaxes[$row['id']] = $row;
            }

            if (!is_null($row['percent'])) {
                $row['percent'] = $row['percent'] ? $row['percent'] : 1;
                $rate = $rate ? $rate : 1;

                $appliedAmount      = $amount/$rate*$row['percent'];
                $baseAppliedAmount  = $baseAmount/$rate*$row['percent'];
            } else {
                $appliedAmount      = 0;
                $baseAppliedAmount  = 0;
                foreach ($row['rates'] as $rate) {
                    $appliedAmount      += $rate['amount'];
                    $baseAppliedAmount  += $rate['base_amount'];
                }
            }


            if ($appliedAmount || $previouslyAppliedTaxes[$row['id']]['amount']) {
                $previouslyAppliedTaxes[$row['id']]['amount']       += $appliedAmount;
                $previouslyAppliedTaxes[$row['id']]['base_amount']  += $baseAppliedAmount;
            } else {
                unset($previouslyAppliedTaxes[$row['id']]);
            }
        }
        $address->setAppliedTaxes($previouslyAppliedTaxes);
    }

    /**
     * Add tax totals information to address object
     *
     * @param   \Magento\Sales\Model\Quote\Address $address
     * @return  \Magento\Tax\Model\Sales\Total\Quote\Tax
     */
    public function fetch(\Magento\Sales\Model\Quote\Address $address)
    {
        $applied    = $address->getAppliedTaxes();
        $store      = $address->getQuote()->getStore();
        $amount     = $address->getTaxAmount();

        $items = $this->_getAddressItems($address);
        $discountTaxCompensation = 0;
        foreach ($items as $item) {
            $discountTaxCompensation += $item->getDiscountTaxCompensation();
        }
        $taxAmount = $amount + $discountTaxCompensation;

        $area       = null;
        if ($this->_config->displayCartTaxWithGrandTotal($store) && $address->getGrandTotal()) {
            $area   = 'taxes';
        }

        if (($amount != 0) || ($this->_config->displayCartZeroTax($store))) {
            $address->addTotal(array(
                'code'      => $this->getCode(),
                'title'     => __('Tax'),
                'full_info' => $applied ? $applied : array(),
                'value'     => $amount,
                'area'      => $area
            ));
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

            $address->addTotal(array(
                'code'      => 'subtotal',
                'title'     => __('Subtotal'),
                'value'     => $subtotalInclTax,
                'value_incl_tax' => $subtotalInclTax,
                'value_excl_tax' => $address->getSubtotal(),
            ));
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
            case \Magento\Tax\Model\Calculation::CALC_TAX_BEFORE_DISCOUNT_ON_INCL:
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
