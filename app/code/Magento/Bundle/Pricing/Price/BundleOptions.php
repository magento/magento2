<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Pricing\Price;

use Magento\Bundle\Pricing\Adjustment\BundleCalculatorInterface;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Catalog\Model\Product;

/**
 * Bundle option price calculation model.
 */
class BundleOptions implements ResetAfterRequestInterface
{
    /**
     * @var BundleCalculatorInterface
     */
    private $calculator;

    /**
     * @var BundleSelectionFactory
     */
    private $selectionFactory;

    /**
     * @var AmountInterface[]
     */
    private $optionSelectionAmountCache = [];

    /**
     * @param BundleCalculatorInterface $calculator
     * @param BundleSelectionFactory $bundleSelectionFactory
     */
    public function __construct(
        BundleCalculatorInterface $calculator,
        BundleSelectionFactory $bundleSelectionFactory
    ) {
        $this->calculator = $calculator;
        $this->selectionFactory = $bundleSelectionFactory;
    }

    /**
     * Get Options with attached Selections collection.
     *
     * @param SaleableInterface $bundleProduct
     * @return \Magento\Bundle\Model\ResourceModel\Option\Collection|array
     */
    public function getOptions(SaleableInterface $bundleProduct)
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
     * Calculate maximal or minimal options value.
     *
     * @param SaleableInterface $bundleProduct
     * @param bool $searchMin
     *
     * @return float
     */
    public function calculateOptions(
        SaleableInterface $bundleProduct,
        bool $searchMin = true
    ) : float {
        $priceList = [];
        /* @var \Magento\Bundle\Model\Option $option */
        foreach ($this->getOptions($bundleProduct) as $option) {
            if ($searchMin && !$option->getRequired()) {
                continue;
            }
            /** @var \Magento\Bundle\Pricing\Price\BundleSelectionPrice $selectionPriceList */
            $selectionPriceList = $this->calculator->createSelectionPriceList($option, $bundleProduct);
            $selectionPriceList = $this->calculator->processOptions($option, $selectionPriceList, $searchMin);
            // phpcs:ignore Magento2.Performance.ForeachArrayMerge
            $priceList = array_merge($priceList, $selectionPriceList);
        }
        $amount = $this->calculator->calculateBundleAmount(0., $bundleProduct, $priceList);

        return $amount->getValue();
    }

    /**
     * Get selection amount.
     *
     * @param Product $bundleProduct
     * @param \Magento\Bundle\Model\Selection|Product $selection
     * @param bool $useRegularPrice
     *
     * @return AmountInterface
     */
    public function getOptionSelectionAmount(
        Product $bundleProduct,
        $selection,
        bool $useRegularPrice = false
    ) : AmountInterface {
        $cacheKey = implode(
            '_',
            [
                $bundleProduct->getId(),
                $selection->getOptionId(),
                $selection->getSelectionId(),
                $useRegularPrice ? 1 : 0,
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

    /**
     * @inheritDoc
     */
    public function _resetState(): void
    {
        $this->optionSelectionAmountCache = [];
    }
}
