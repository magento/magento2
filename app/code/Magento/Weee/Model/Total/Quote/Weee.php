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
namespace Magento\Weee\Model\Total\Quote;

use Magento\Store\Model\Store;
use Magento\Tax\Model\Calculation;

class Weee extends \Magento\Tax\Model\Sales\Total\Quote\Tax
{
    /**
     * @var \Magento\Weee\Helper\Data
     */
    protected $_weeeData;
    
    /**
     * @var \Magento\Store\Model\Store
     */
    protected $_store;

    /**
     * @param \Magento\Tax\Helper\Data $taxData
     * @param \Magento\Tax\Model\Calculation $calculation
     * @param \Magento\Tax\Model\Config $taxConfig
     * @param \Magento\Weee\Helper\Data $_weeeData
     */
    public function __construct(
        \Magento\Tax\Helper\Data $taxData,
        \Magento\Tax\Model\Calculation $calculation,
        \Magento\Tax\Model\Config $taxConfig,
        \Magento\Weee\Helper\Data $_weeeData
    ) {
        $this->_weeeData = $_weeeData;
        parent::__construct($taxData, $calculation, $taxConfig);
        $this->setCode('weee');
    }

    /**
     * Collect Weee taxes amount and prepare items prices for taxation and discount
     *
     * @param   \Magento\Sales\Model\Quote\Address $address
     * @return  $this
     */
    public function collect(\Magento\Sales\Model\Quote\Address $address)
    {
        \Magento\Sales\Model\Quote\Address\Total\AbstractTotal::collect($address);
        $items = $this->_getAddressItems($address);
        if (!count($items)) {
            return $this;
        }

        $address->setAppliedTaxesReset(true);
        $address->setAppliedTaxes(array());

        $this->_store = $address->getQuote()->getStore();
        foreach ($items as $item) {
            if ($item->getParentItemId()) {
                continue;
            }
            $this->_resetItemData($item);
            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                foreach ($item->getChildren() as $child) {
                    $this->_resetItemData($child);
                    $this->_process($address, $child);
                }
                $this->_recalculateParent($item);
            } else {
                $this->_process($address, $item);
            }
        }

        return $this;
    }

    /**
     * Calculate item fixed tax and prepare information for discount and regular taxation
     *
     * @param   \Magento\Sales\Model\Quote\Address $address
     * @param   \Magento\Sales\Model\Quote\Item\AbstractItem $item
     * @return  void|$this
     */
    protected function _process(\Magento\Sales\Model\Quote\Address $address, $item)
    {
        if (!$this->_weeeData->isEnabled($this->_store)) {
            return $this;
        }

        $attributes = $this->_weeeData->getProductWeeeAttributes(
            $item->getProduct(),
            $address,
            $address->getQuote()->getBillingAddress(),
            $this->_store->getWebsiteId()
        );

        $applied = array();
        $productTaxes = array();

        $defaultRateRequest = $this->_calculator->getRateOriginRequest($this->_store);
        $rateRequest = $this->_calculator->getRateRequest(
            $address,
            $address->getQuote()->getBillingAddress(),
            $address->getQuote()->getCustomerTaxClassId(),
            $this->_store
        );

        $totalValueInclTax = 0;
        $baseTotalValueInclTax = 0;
        $totalRowValueInclTax = 0;
        $baseTotalRowValueInclTax = 0;

        $totalValueExclTax = 0;
        $baseTotalValueExclTax = 0;
        $totalRowValueExclTax = 0;
        $baseTotalRowValueExclTax = 0;

        $priceIncludesTax = $this->_taxData->priceIncludesTax($this->_store);
        $calculationAlgorithm = $this->_taxData->getCalculationAgorithm($this->_store);
        $defaultPercent = $currentPercent = 0; //when FPT is not taxable
        foreach ($attributes as $key => $attribute) {
            $title          = $attribute->getName();

            $baseValue = $attribute->getAmount();
            $value = $this->_store->convertPrice($baseValue);
            $value = $this->_store->roundPrice($value);

            if ($this->_weeeData->isTaxable($this->_store)) {
                $defaultPercent = $this->_calculator->getRate(
                    $defaultRateRequest->setProductClassId($item->getProduct()->getTaxClassId())
                );
                $currentPercent = $this->_calculator->getRate(
                    $rateRequest->setProductClassId($item->getProduct()->getTaxClassId())
                );
            }

            if ($priceIncludesTax) {
                //Make sure that price including tax is rounded first
                $baseValueInclTax = $baseValue / (100 + $defaultPercent) * (100 + $currentPercent);
                $baseValueInclTax = $this->_store->roundPrice($baseValueInclTax);
                $valueInclTax = $value / (100 + $defaultPercent) * (100 + $currentPercent);
                $valueInclTax = $this->_store->roundPrice($valueInclTax);

                $baseValueExclTax = $baseValueInclTax / (100 + $currentPercent) * 100;
                $valueExclTax = $valueInclTax / (100 + $currentPercent) * 100;
                if ($calculationAlgorithm == Calculation::CALC_UNIT_BASE) {
                    $baseValueExclTax = $this->_store->roundPrice($baseValueExclTax);
                    $valueExclTax = $this->_store->roundPrice($valueExclTax);
                }
            } else {
                $valueExclTax = $value;
                $baseValueExclTax = $baseValue;

                $valueInclTax = $valueExclTax * (100 + $currentPercent) / 100;
                $baseValueInclTax = $baseValueExclTax * (100 + $currentPercent) / 100;
                if ($calculationAlgorithm == Calculation::CALC_UNIT_BASE) {
                    $baseValueInclTax = $this->_store->roundPrice($baseValueInclTax);
                    $valueInclTax = $this->_store->roundPrice($valueInclTax);
                }
            }

            $rowValueInclTax       = $this->_store->roundPrice($valueInclTax * $item->getTotalQty());
            $baseRowValueInclTax   = $this->_store->roundPrice($baseValueInclTax * $item->getTotalQty());
            $rowValueExclTax = $this->_store->roundPrice($valueExclTax * $item->getTotalQty());
            $baseRowValueExclTax = $this->_store->roundPrice($baseValueExclTax * $item->getTotalQty());

            //Now, round the unit price just in case
            $valueExclTax = $this->_store->roundPrice($valueExclTax);
            $baseValueExclTax = $this->_store->roundPrice($baseValueExclTax);
            $valueInclTax = $this->_store->roundPrice($valueInclTax);
            $baseValueInclTax = $this->_store->roundPrice($baseValueInclTax);

            $totalValueInclTax += $valueInclTax;
            $baseTotalValueInclTax += $baseValueInclTax;
            $totalRowValueInclTax += $rowValueInclTax;
            $baseTotalRowValueInclTax += $baseRowValueInclTax;


            $totalValueExclTax += $valueExclTax;
            $baseTotalValueExclTax += $baseValueExclTax;
            $totalRowValueExclTax += $rowValueExclTax;
            $baseTotalRowValueExclTax += $baseRowValueExclTax;

            $productTaxes[] = array(
                'title' => $title,
                'base_amount' => $baseValueExclTax,
                'amount' => $valueExclTax,
                'row_amount' => $rowValueExclTax,
                'base_row_amount' => $baseRowValueExclTax,
                'base_amount_incl_tax' => $baseValueInclTax,
                'amount_incl_tax' => $valueInclTax,
                'row_amount_incl_tax' => $rowValueInclTax,
                'base_row_amount_incl_tax' => $baseRowValueInclTax,
            );

            //This include FPT as applied tax, since tax on FPT is calculated separately, we use value excluding tax
            $applied[] = array(
                'id'        => $attribute->getCode(),
                'percent'   => null,
                'hidden'    => $this->_weeeData->includeInSubtotal($this->_store),
                'rates'     => array(array(
                    'base_real_amount'=> $baseRowValueExclTax,
                    'base_amount'   => $baseRowValueExclTax,
                    'amount'        => $rowValueExclTax,
                    'code'          => $attribute->getCode(),
                    'title'         => $title,
                    'percent'       => null,
                    'position'      => 1,
                    'priority'      => -1000 + $key,
                ))
            );
        }

        $item->setWeeeTaxAppliedAmount($totalValueExclTax)
            ->setBaseWeeeTaxAppliedAmount($baseTotalValueExclTax)
            ->setWeeeTaxAppliedRowAmount($totalRowValueExclTax)
            ->setBaseWeeeTaxAppliedRowAmnt($baseTotalRowValueExclTax);

        $item->setWeeeTaxAppliedAmountInclTax($totalValueInclTax)
            ->setBaseWeeeTaxAppliedAmountInclTax($baseTotalValueInclTax)
            ->setWeeeTaxAppliedRowAmountInclTax($totalRowValueInclTax)
            ->setBaseWeeeTaxAppliedRowAmntInclTax($baseTotalRowValueInclTax);

        if ($priceIncludesTax) {
            $this->_processTaxSettings(
                $item,
                $totalValueInclTax,
                $baseTotalValueInclTax,
                $totalRowValueInclTax,
                $baseTotalRowValueInclTax
            );
        } else {
            $this->_processTaxSettings(
                $item,
                $totalValueExclTax,
                $baseTotalValueExclTax,
                $totalRowValueExclTax,
                $baseTotalRowValueExclTax
            );
        }
        $this->_processTotalAmount(
            $address,
            $totalRowValueExclTax,
            $baseTotalRowValueExclTax,
            $totalRowValueInclTax,
            $baseTotalRowValueInclTax
        );

        $this->_weeeData->setApplied($item, array_merge($this->_weeeData->getApplied($item), $productTaxes));

        //Update the applied taxes for the quote
        if ($applied) {
            $this->_saveAppliedTaxes(
                $address,
                $applied,
                $item->getWeeeTaxAppliedAmount(),
                $item->getBaseWeeeTaxAppliedAmount(),
                null
            );
        }
    }

    /**
     * Check if discount should be applied to weee and add weee to discounted price
     *
     * @deprecated 
     * @param   \Magento\Sales\Model\Quote\Item\AbstractItem $item
     * @param   float $value
     * @param   float $baseValue
     * @return  $this
     */
    protected function _processDiscountSettings($item, $value, $baseValue)
    {
        if ($this->_weeeData->isDiscounted($this->_store)) {
            $this->_weeeData->addItemDiscountPrices($item, $baseValue, $value);
        }
        return $this;
    }

    /**
     * Add extra amount which should be taxable by regular tax
     *
     * @param   \Magento\Sales\Model\Quote\Item\AbstractItem $item
     * @param   float $value
     * @param   float $baseValue
     * @param   float $rowValue
     * @param   float $baseRowValue
     * @return  $this
     */
    protected function _processTaxSettings($item, $value, $baseValue, $rowValue, $baseRowValue)
    {
        if ($this->_weeeData->isTaxable($this->_store) && $rowValue) {
            $item->setExtraTaxableAmount($value)
                ->setBaseExtraTaxableAmount($baseValue)
                ->setExtraRowTaxableAmount($rowValue)
                ->setBaseExtraRowTaxableAmount($baseRowValue);
        }
        return $this;
    }

    /**
     * Process row amount based on FPT total amount configuration setting
     *
     * @param   \Magento\Sales\Model\Quote\Address $address
     * @param   float $rowValueExclTax
     * @param   float $baseRowValueExclTax
     * @param   float $rowValueInclTax
     * @param   float $baseRowValueInclTax
     * @return  $this
     */
    protected function _processTotalAmount($address, $rowValueExclTax, $baseRowValueExclTax, $rowValueInclTax, $baseRowValueInclTax)
    {
        if ($this->_weeeData->includeInSubtotal($this->_store)) {
            $address->addTotalAmount('subtotal', $this->_store->roundPrice($rowValueExclTax));
            $address->addBaseTotalAmount('subtotal', $this->_store->roundPrice($baseRowValueExclTax));
        } else {
            $address->setExtraTaxAmount($address->getExtraTaxAmount() + $rowValueExclTax);
            $address->setBaseExtraTaxAmount($address->getBaseExtraTaxAmount() + $baseRowValueExclTax);
        }
        $address->setSubtotalInclTax($address->getSubtotalInclTax() + $this->_store->roundPrice($rowValueInclTax));
        $address->setBaseSubtotalInclTax($address->getBaseSubtotalInclTax() + $this->_store->roundPrice($baseRowValueInclTax));
        return $this;
    }

    /**
     * Recalculate parent item amounts based on children results
     *
     * @param   \Magento\Sales\Model\Quote\Item\AbstractItem $item
     * @return  void
     * 
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _recalculateParent(\Magento\Sales\Model\Quote\Item\AbstractItem $item)
    {
    }

    /**
     * Reset information about FPT for shopping cart item
     *
     * @param   \Magento\Sales\Model\Quote\Item\AbstractItem $item
     * @return  void
     */
    protected function _resetItemData($item)
    {
        $this->_weeeData->setApplied($item, array());

        $item->setBaseWeeeTaxDisposition(0);
        $item->setWeeeTaxDisposition(0);

        $item->setBaseWeeeTaxRowDisposition(0);
        $item->setWeeeTaxRowDisposition(0);

        $item->setBaseWeeeTaxAppliedAmount(0);
        $item->setBaseWeeeTaxAppliedRowAmnt(0);

        $item->setWeeeTaxAppliedAmount(0);
        $item->setWeeeTaxAppliedRowAmount(0);
    }

    /**
     * Fetch FPT data to address object for display in totals block
     *
     * @param   \Magento\Sales\Model\Quote\Address $address
     * @return  $this
     * 
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function fetch(\Magento\Sales\Model\Quote\Address $address)
    {
        return $this;
    }

    /**
     * Process model configuration array.
     * This method can be used for changing totals collect sort order
     *
     * @param   array $config
     * @param   Store $store
     * @return  array
     * 
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function processConfigArray($config, $store)
    {
        return $config;
    }

    /**
     * No aggregated label for fixed product tax
     *
     * TODO: fix
     * @return string
     */
    public function getLabel()
    {
        return '';
    }
}
