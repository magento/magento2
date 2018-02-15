<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Pricing\Price;

/**
 * Bundle option price calculation model.
 */
class BundleOptions
{
    /**
     * @var \Magento\Bundle\Pricing\Adjustment\BundleCalculatorInterface
     */
    private $calculator;

    /**
     * @var BundleSelectionFactory
     */
    private $selectionFactory;

    /**
     * @var \Magento\Framework\Pricing\Amount\AmountInterface[]
     */
    private $optionSelectionAmountCache = [];

    /**
     * @param \Magento\Bundle\Pricing\Adjustment\BundleCalculatorInterface $calculator
     * @param BundleSelectionFactory $bundleSelectionFactory
     */
    public function __construct(
        \Magento\Bundle\Pricing\Adjustment\BundleCalculatorInterface $calculator,
        BundleSelectionFactory $bundleSelectionFactory
    ) {
        $this->calculator = $calculator;
        $this->selectionFactory = $bundleSelectionFactory;
    }

    /**
     * Get Options with attached Selections collection
     *
     * @param \Magento\Framework\Pricing\SaleableInterface $bundleProduct
     * @return \Magento\Bundle\Model\ResourceModel\Option\Collection
     */
    public function getOptions(\Magento\Framework\Pricing\SaleableInterface $bundleProduct)
    {
        /** @var \Magento\Bundle\Model\Product\Type $typeInstance */
        $typeInstance = $bundleProduct->getTypeInstance();
        $typeInstance->setStoreFilter($bundleProduct->getStoreId(), $bundleProduct);

        /** @var \Magento\Bundle\Model\ResourceModel\Option\Collection $optionCollection */
        $optionCollection = $typeInstance->getOptionsCollection($bundleProduct);

        /** @var \Magento\Bundle\Model\ResourceModel\Selection\Collection $selectionCollection */
        $selectionCollection = $typeInstance->getSelectionsCollection(
            $typeInstance->getOptionsIds($bundleProduct),
            $bundleProduct
        );

        $priceOptions = $optionCollection->appendSelections($selectionCollection, true, false);
        return $priceOptions;
    }

    /**
     * Calculate maximal or minimal options value
     *
     * @param \Magento\Framework\Pricing\SaleableInterface $bundleProduct
     * @param bool $searchMin
     * @return float
     */
    public function calculateOptions(
        \Magento\Framework\Pricing\SaleableInterface $bundleProduct,
        bool $searchMin = true
    ) {
        $priceList = [];
        /* @var $option \Magento\Bundle\Model\Option */
        foreach ($this->getOptions($bundleProduct) as $option) {
            if ($searchMin && !$option->getRequired()) {
                continue;
            }
            /** @var \Magento\Bundle\Pricing\Price\BundleSelectionPrice $selectionPriceList */
            $selectionPriceList = $this->calculator->createSelectionPriceList($option, $bundleProduct);
            $selectionPriceList = $this->calculator->processOptions($option, $selectionPriceList, $searchMin);
            $priceList = array_merge($priceList, $selectionPriceList);
        }
        $amount = $this->calculator->calculateBundleAmount(0., $bundleProduct, $priceList);
        return $amount->getValue();
    }

    /**
     * Get selection amount
     *
     * @param \Magento\Catalog\Model\Product $bundleProduct
     * @param \Magento\Bundle\Model\Selection $selection
     * @param bool $useRegularPrice
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     */
    public function getOptionSelectionAmount(
        \Magento\Catalog\Model\Product $bundleProduct,
        $selection,
        bool $useRegularPrice = false
    ) {
        $cacheKey = implode(
            '_',
            [
                $bundleProduct->getId(),
                $selection->getOptionId(),
                $selection->getSelectionId(),
                $useRegularPrice ? 1 : 0
            ]
        );

        if (!isset($this->optionSelectionAmountCache[$cacheKey])) {
            $selectionPrice = $this->selectionFactory
                ->create(
                    $bundleProduct,
                    $selection,
                    $selection->getSelectionQty(),
                    ['useRegularPrice' => $useRegularPrice]
                );
            $this->optionSelectionAmountCache[$cacheKey] =  $selectionPrice->getAmount();
        }

        return $this->optionSelectionAmountCache[$cacheKey];
    }
}
