<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Pricing\Adjustment;

use Magento\Bundle\Model\Product\Price;
use Magento\Bundle\Pricing\Price\BundleSelectionFactory;
use Magento\Catalog\Model\Product;
use Magento\Framework\Pricing\Adjustment\Calculator as CalculatorBase;
use Magento\Framework\Pricing\Amount\AmountFactory;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Store\Model\Store;
use Magento\Tax\Api\TaxCalculationInterface;
use Magento\Tax\Helper\Data as TaxHelper;

/**
 * Bundle price calculator
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Calculator implements BundleCalculatorInterface
{
    /**
     * @var CalculatorBase
     */
    protected $calculator;

    /**
     * @var AmountFactory
     */
    protected $amountFactory;

    /**
     * @var BundleSelectionFactory
     */
    protected $selectionFactory;

    /**
     * Tax helper, needed to get rounding setting
     *
     * @var TaxHelper
     */
    protected $taxHelper;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var \Magento\Framework\Pricing\Amount\AmountInterface[]
     */
    private $optionAmount = [];

    /**
     * @var SelectionPriceListProviderInterface
     */
    private $selectionPriceListProvider;

    /**
     * @param CalculatorBase $calculator
     * @param AmountFactory $amountFactory
     * @param BundleSelectionFactory $bundleSelectionFactory
     * @param TaxHelper $taxHelper
     * @param PriceCurrencyInterface $priceCurrency
     * @param SelectionPriceListProviderInterface|null $selectionPriceListProvider
     */
    public function __construct(
        CalculatorBase $calculator,
        AmountFactory $amountFactory,
        BundleSelectionFactory $bundleSelectionFactory,
        TaxHelper $taxHelper,
        PriceCurrencyInterface $priceCurrency,
        SelectionPriceListProviderInterface $selectionPriceListProvider = null
    ) {
        $this->calculator = $calculator;
        $this->amountFactory = $amountFactory;
        $this->selectionFactory = $bundleSelectionFactory;
        $this->taxHelper = $taxHelper;
        $this->priceCurrency = $priceCurrency;
        $this->selectionPriceListProvider = $selectionPriceListProvider;
    }

    /**
     * Get amount for current product which is included price of existing options with minimal price
     *
     * @param float|string $amount
     * @param SaleableInterface $saleableItem
     * @param null|bool|string|array $exclude
     * @param null|array $context
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getAmount($amount, SaleableInterface $saleableItem, $exclude = null, $context = [])
    {
        return $this->getOptionsAmount($saleableItem, $exclude, true, $amount);
    }

    /**
     * Get amount for current product which is included price of existing options with maximal price
     *
     * @param float $amount
     * @param Product $saleableItem
     * @param null|bool|string|array $exclude
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     */
    public function getMinRegularAmount($amount, Product $saleableItem, $exclude = null)
    {
        return $this->getOptionsAmount($saleableItem, $exclude, true, $amount, true);
    }

    /**
     * Get amount for current product which is included price of existing options with maximal price
     *
     * @param float $amount
     * @param Product $saleableItem
     * @param null|bool|string|array $exclude
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     */
    public function getMaxAmount($amount, Product $saleableItem, $exclude = null)
    {
        return $this->getOptionsAmount($saleableItem, $exclude, false, $amount);
    }

    /**
     * Get amount for current product which is included price of existing options with maximal price
     *
     * @param float $amount
     * @param Product $saleableItem
     * @param null|bool|string|array $exclude
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     */
    public function getMaxRegularAmount($amount, Product $saleableItem, $exclude = null)
    {
        return $this->getOptionsAmount($saleableItem, $exclude, false, $amount, true);
    }

    /**
     * Option amount calculation for bundle product
     *
     * @param Product $saleableItem
     * @param null|bool|string|array $exclude
     * @param bool $searchMin
     * @param float $baseAmount
     * @param bool $useRegularPrice
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     */
    public function getOptionsAmount(
        Product $saleableItem,
        $exclude = null,
        $searchMin = true,
        $baseAmount = 0.,
        $useRegularPrice = false
    ) {
        $cacheKey = implode('-', [$saleableItem->getId(), $exclude, $searchMin, $baseAmount, $useRegularPrice]);
        if (!isset($this->optionAmount[$cacheKey])) {
            $this->optionAmount[$cacheKey] = $this->calculateBundleAmount(
                $baseAmount,
                $saleableItem,
                $this->getSelectionAmounts($saleableItem, $searchMin, $useRegularPrice),
                $exclude
            );
        }

        return $this->optionAmount[$cacheKey];
    }

    /**
     * Get base amount without option
     *
     * @param float $amount
     * @param Product $saleableItem
     * @return \Magento\Framework\Pricing\Amount\AmountInterface|void
     */
    public function getAmountWithoutOption($amount, Product $saleableItem)
    {
        return $this->calculateBundleAmount(
            $amount,
            $saleableItem,
            []
        );
    }

    /**
     * Filter all options for bundle product
     *
     * @param Product $bundleProduct
     * @param bool $searchMin
     * @param bool $useRegularPrice
     * @return array
     */
    protected function getSelectionAmounts(Product $bundleProduct, $searchMin, $useRegularPrice = false)
    {
        return $this->getSelectionPriceListProvider()->getPriceList($bundleProduct, $searchMin, $useRegularPrice);
    }

    /**
     * @return SelectionPriceListProviderInterface
     * @deprecated
     */
    private function getSelectionPriceListProvider()
    {
        if (null === $this->selectionPriceListProvider) {
            $this->selectionPriceListProvider = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(SelectionPriceListProviderInterface::class);
        }

        return $this->selectionPriceListProvider;
    }

    /**
     * Check this option if it should be skipped
     *
     * @param \Magento\Bundle\Model\Option $option
     * @param bool $canSkipRequiredOption
     * @return bool
     * @deprecated
     */
    protected function canSkipOption($option, $canSkipRequiredOption)
    {
        return !$option->getSelections() || ($canSkipRequiredOption && !$option->getRequired());
    }

    /**
     * Check the bundle product for availability of required options
     *
     * @param Product $bundleProduct
     * @return bool
     * @deprecated
     */
    protected function hasRequiredOption($bundleProduct)
    {
        $options = array_filter(
            $this->getBundleOptions($bundleProduct),
            function ($item) {
                return $item->getRequired();
            }
        );
        return !empty($options);
    }

    /**
     * Get bundle options
     *
     * @param Product $saleableItem
     * @return \Magento\Bundle\Model\ResourceModel\Option\Collection
     * @deprecated
     */
    protected function getBundleOptions(Product $saleableItem)
    {
        /** @var \Magento\Bundle\Pricing\Price\BundleOptionPrice $bundlePrice */
        $bundlePrice = $saleableItem->getPriceInfo()->getPrice(
            \Magento\Bundle\Pricing\Price\BundleOptionPrice::PRICE_CODE
        );
        return $bundlePrice->getOptions();
    }

    /**
     * Calculate amount for bundle product with all selection prices
     *
     * @param float $basePriceValue
     * @param Product $bundleProduct
     * @param \Magento\Bundle\Pricing\Price\BundleSelectionPrice[] $selectionPriceList
     * @param null|bool|string|array $exclude
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     */
    public function calculateBundleAmount($basePriceValue, $bundleProduct, $selectionPriceList, $exclude = null)
    {
        if ($bundleProduct->getPriceType() == Price::PRICE_TYPE_FIXED) {
            return $this->calculateFixedBundleAmount($basePriceValue, $bundleProduct, $selectionPriceList, $exclude);
        } else {
            return $this->calculateDynamicBundleAmount($basePriceValue, $bundleProduct, $selectionPriceList, $exclude);
        }
    }

    /**
     * Calculate amount for fixed bundle product
     *
     * @param float $basePriceValue
     * @param Product $bundleProduct
     * @param \Magento\Bundle\Pricing\Price\BundleSelectionPrice[] $selectionPriceList
     * @param null|bool|string|arrayy $exclude
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     */
    protected function calculateFixedBundleAmount($basePriceValue, $bundleProduct, $selectionPriceList, $exclude)
    {
        $fullAmount = $basePriceValue;
        /** @var $option \Magento\Bundle\Model\Option */
        foreach ($selectionPriceList as $selectionPrice) {
            $fullAmount += ($selectionPrice->getValue() * $selectionPrice->getQuantity());
        }
        return $this->calculator->getAmount($fullAmount, $bundleProduct, $exclude);
    }

    /**
     * Calculate amount for dynamic bundle product
     *
     * @param float $basePriceValue
     * @param Product $bundleProduct
     * @param \Magento\Bundle\Pricing\Price\BundleSelectionPrice[] $selectionPriceList
     * @param null|bool|string|array $exclude
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function calculateDynamicBundleAmount($basePriceValue, $bundleProduct, $selectionPriceList, $exclude)
    {
        $fullAmount = 0.;
        $adjustments = [];
        $i = 0;

        $amountList[$i]['amount'] = $this->calculator->getAmount($basePriceValue, $bundleProduct, $exclude);
        $amountList[$i]['quantity'] = 1;

        foreach ($selectionPriceList as $selectionPrice) {
            ++$i;
            $amountList[$i]['amount'] = $selectionPrice->getAmount();
            // always honor the quantity given
            $amountList[$i]['quantity'] = $selectionPrice->getQuantity();
        }

        /** @var  Store $store */
        $store = $bundleProduct->getStore();
        $roundingMethod = $this->taxHelper->getCalculationAlgorithm($store);
        foreach ($amountList as $amountInfo) {
            /** @var \Magento\Framework\Pricing\Amount\AmountInterface $itemAmount */
            $itemAmount = $amountInfo['amount'];
            $qty = $amountInfo['quantity'];

            if ($roundingMethod != TaxCalculationInterface::CALC_TOTAL_BASE) {
                //We need to round the individual selection first
                $fullAmount += ($this->priceCurrency->round($itemAmount->getValue()) * $qty);
                foreach ($itemAmount->getAdjustmentAmounts() as $code => $adjustment) {
                    $adjustment = $this->priceCurrency->round($adjustment) * $qty;
                    $adjustments[$code] = isset($adjustments[$code]) ? $adjustments[$code] + $adjustment : $adjustment;
                }
            } else {
                $fullAmount += ($itemAmount->getValue() * $qty);
                foreach ($itemAmount->getAdjustmentAmounts() as $code => $adjustment) {
                    $adjustment = $adjustment * $qty;
                    $adjustments[$code] = isset($adjustments[$code]) ? $adjustments[$code] + $adjustment : $adjustment;
                }
            }
        }
        if (is_array($exclude) == false) {
            if ($exclude && isset($adjustments[$exclude])) {
                $fullAmount -= $adjustments[$exclude];
                unset($adjustments[$exclude]);
            }
        } else {
            foreach ($exclude as $oneExclusion) {
                if ($oneExclusion && isset($adjustments[$oneExclusion])) {
                    $fullAmount -= $adjustments[$oneExclusion];
                    unset($adjustments[$oneExclusion]);
                }
            }
        }
        return $this->amountFactory->create($fullAmount, $adjustments);
    }

    /**
     * Create selection price list for the retrieved options
     *
     * @param \Magento\Bundle\Model\Option $option
     * @param Product $bundleProduct
     * @param bool $useRegularPrice
     * @return \Magento\Bundle\Pricing\Price\BundleSelectionPrice[]
     */
    public function createSelectionPriceList($option, $bundleProduct, $useRegularPrice = false)
    {
        $priceList = [];
        $selections = $option->getSelections();
        if ($selections === null) {
            return $priceList;
        }
        /* @var $selection \Magento\Bundle\Model\Selection|\Magento\Catalog\Model\Product */
        foreach ($selections as $selection) {
            if (!$selection->isSalable()) {
                // @todo CatalogInventory Show out of stock Products
                continue;
            }
            $priceList[] = $this->selectionFactory->create(
                $bundleProduct,
                $selection,
                $selection->getSelectionQty(),
                [
                    'useRegularPrice' => $useRegularPrice,
                ]
            );
        }
        return $priceList;
    }

    /**
     * Find minimal or maximal price for existing options
     *
     * @param \Magento\Bundle\Model\Option $option
     * @param \Magento\Bundle\Pricing\Price\BundleSelectionPrice[] $selectionPriceList
     * @param bool $searchMin
     * @return \Magento\Bundle\Pricing\Price\BundleSelectionPrice[]
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function processOptions($option, $selectionPriceList, $searchMin = true)
    {
        $result = [];
        foreach ($selectionPriceList as $current) {
            $qty = $current->getQuantity();
            $currentValue = $current->getAmount()->getValue() * $qty;
            if (empty($result)) {
                $result = [$current];
            } else {
                $lastSelectionPrice = end($result);
                $lastValue = $lastSelectionPrice->getAmount()->getValue() * $lastSelectionPrice->getQuantity();
                if ($searchMin && $lastValue > $currentValue) {
                    $result = [$current];
                } elseif (!$searchMin && $option->isMultiSelection()) {
                    $result[] = $current;
                } elseif (!$searchMin
                    && !$option->isMultiSelection()
                    && $lastValue < $currentValue
                ) {
                    $result = [$current];
                }
            }
        }
        return $result;
    }
}
