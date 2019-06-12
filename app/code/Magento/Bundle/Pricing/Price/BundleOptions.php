<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Pricing\Price;

<<<<<<< HEAD
=======
use Magento\Bundle\Pricing\Adjustment\BundleCalculatorInterface;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Catalog\Model\Product;

>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
/**
 * Bundle option price calculation model.
 */
class BundleOptions
{
    /**
<<<<<<< HEAD
     * @var \Magento\Bundle\Pricing\Adjustment\BundleCalculatorInterface
=======
     * @var BundleCalculatorInterface
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     */
    private $calculator;

    /**
     * @var BundleSelectionFactory
     */
    private $selectionFactory;

    /**
<<<<<<< HEAD
     * @var \Magento\Framework\Pricing\Amount\AmountInterface[]
=======
     * @var AmountInterface[]
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     */
    private $optionSelectionAmountCache = [];

    /**
<<<<<<< HEAD
     * @param \Magento\Bundle\Pricing\Adjustment\BundleCalculatorInterface $calculator
     * @param BundleSelectionFactory $bundleSelectionFactory
     */
    public function __construct(
        \Magento\Bundle\Pricing\Adjustment\BundleCalculatorInterface $calculator,
=======
     * @param BundleCalculatorInterface $calculator
     * @param BundleSelectionFactory $bundleSelectionFactory
     */
    public function __construct(
        BundleCalculatorInterface $calculator,
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
        BundleSelectionFactory $bundleSelectionFactory
    ) {
        $this->calculator = $calculator;
        $this->selectionFactory = $bundleSelectionFactory;
    }

    /**
<<<<<<< HEAD
     * Get Options with attached Selections collection
     *
     * @param \Magento\Framework\Pricing\SaleableInterface $bundleProduct
     * @return \Magento\Bundle\Model\ResourceModel\Option\Collection
     */
    public function getOptions(\Magento\Framework\Pricing\SaleableInterface $bundleProduct)
=======
     * Get Options with attached Selections collection.
     *
     * @param SaleableInterface $bundleProduct
     * @return \Magento\Bundle\Model\ResourceModel\Option\Collection|array
     */
    public function getOptions(SaleableInterface $bundleProduct)
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
<<<<<<< HEAD
=======

>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
        return $priceOptions;
    }

    /**
<<<<<<< HEAD
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
=======
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
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
<<<<<<< HEAD
=======

>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
        return $amount->getValue();
    }

    /**
<<<<<<< HEAD
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
=======
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
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
        $cacheKey = implode(
            '_',
            [
                $bundleProduct->getId(),
                $selection->getOptionId(),
                $selection->getSelectionId(),
<<<<<<< HEAD
                $useRegularPrice ? 1 : 0
=======
                $useRegularPrice ? 1 : 0,
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
