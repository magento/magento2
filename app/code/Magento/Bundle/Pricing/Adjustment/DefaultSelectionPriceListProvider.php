<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Pricing\Adjustment;

use Magento\Bundle\Model\Option;
use Magento\Bundle\Pricing\Price\BundleSelectionFactory;
use Magento\Catalog\Model\Product;
use Magento\Bundle\Model\Product\Price;

/**
 * Provide lightweight implementation which uses price index
 */
class DefaultSelectionPriceListProvider implements SelectionPriceListProviderInterface
{
    /**
     * @var BundleSelectionFactory
     */
    private $selectionFactory;

    /**
     * @param BundleSelectionFactory $bundleSelectionFactory
     */
    public function __construct(BundleSelectionFactory $bundleSelectionFactory)
    {
        $this->selectionFactory = $bundleSelectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getPriceList(Product $bundleProduct, $searchMin, $useRegularPrice)
    {
        $shouldFindMinOption = $this->isShouldFindMinOption($bundleProduct, $searchMin);
        $canSkipRequiredOptions = $searchMin && !$shouldFindMinOption;

        /** @var \Magento\Bundle\Model\Product\Type $typeInstance */
        $typeInstance = $bundleProduct->getTypeInstance();
        $priceList = [];

        foreach ($this->getBundleOptions($bundleProduct) as $option) {
            /** @var Option $option */
            if ($this->canSkipOption($option, $canSkipRequiredOptions)) {
                continue;
            }

            $selectionsCollection = $typeInstance->getSelectionsCollection(
                [(int)$option->getOptionId()],
                $bundleProduct
            );
            $selectionsCollection->removeAttributeToSelect();
            $selectionsCollection->addQuantityFilter();

            if (!$searchMin && $option->isMultiSelection()) {
                $priceList = array_merge(
                    $priceList,
                    $this->getMaximumMultiselectionPriceList($bundleProduct, $selectionsCollection, $useRegularPrice)
                );
            } else {
                $priceList = array_merge(
                    $priceList,
                    $this->getMiniMaxPriceList($bundleProduct, $selectionsCollection, $searchMin, $useRegularPrice)
                );
            }
        }

        if ($shouldFindMinOption) {
            $priceList = $this->getMinPriceForNonRequiredOptions($priceList);
        }

        return $priceList;
    }

    /**
     * Flag shows - is it necessary to find minimal option amount in case if all options are not required
     *
     * @param Product $bundleProduct
     * @param bool $searchMin
     * @return bool
     */
    private function isShouldFindMinOption(Product $bundleProduct, $searchMin)
    {
        $shouldFindMinOption = false;
        if ($searchMin
            && $bundleProduct->getPriceType() == Price::PRICE_TYPE_DYNAMIC
            && !$this->hasRequiredOption($bundleProduct)
        ) {
            $shouldFindMinOption = true;
        }

        return $shouldFindMinOption;
    }

    /**
     * Get minimum or maximum price for option
     *
     * @param Product $bundleProduct
     * @param \Magento\Bundle\Model\ResourceModel\Selection\Collection $selectionsCollection
     * @param bool $searchMin
     * @param bool $useRegularPrice
     * @return \Magento\Bundle\Pricing\Price\BundleSelectionPrice[]
     */
    private function getMiniMaxPriceList(Product $bundleProduct, $selectionsCollection, $searchMin, $useRegularPrice)
    {
        $priceList = [];

        $selectionsCollection->addPriceFilter($bundleProduct, $searchMin, $useRegularPrice);
        $selectionsCollection->setPage(0, 1);
        if (!$useRegularPrice) {
            $selectionsCollection->addAttributeToSelect('special_price');
            $selectionsCollection->addAttributeToSelect('special_price_from');
            $selectionsCollection->addAttributeToSelect('special_price_to');
            $selectionsCollection->addAttributeToSelect('tax_class_id');
        }

        $selection = $selectionsCollection->getFirstItem();

        if (!$selection->isEmpty()) {
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
     * Get maximum price for multiselection option
     *
     * @param Product $bundleProduct
     * @param \Magento\Bundle\Model\ResourceModel\Selection\Collection $selectionsCollection
     * @param bool $useRegularPrice
     * @return \Magento\Bundle\Pricing\Price\BundleSelectionPrice[]
     */
    private function getMaximumMultiselectionPriceList(Product $bundleProduct, $selectionsCollection, $useRegularPrice)
    {
        $priceList = [];

        $selectionsCollection->addPriceData();
        foreach ($selectionsCollection as $selection) {
            $priceList[] =  $this->selectionFactory->create(
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
     * @param \Magento\Bundle\Pricing\Price\BundleSelectionPrice[] $priceList
     * @return \Magento\Bundle\Pricing\Price\BundleSelectionPrice[]
     */
    private function getMinPriceForNonRequiredOptions($priceList)
    {
        $minPrice = null;
        $priceSelection = null;
        foreach ($priceList as $price) {
            $minPriceTmp = $price->getAmount()->getValue() * $price->getQuantity();
            if (!$minPrice || $minPriceTmp < $minPrice) {
                $minPrice = $minPriceTmp;
                $priceSelection = $price;
            }
        }
        $priceList = $priceSelection ? [$priceSelection] : [];

        return $priceList;
    }

    /**
     * Check this option if it should be skipped
     *
     * @param Option $option
     * @param bool $canSkipRequiredOption
     * @return bool
     */
    private function canSkipOption($option, $canSkipRequiredOption)
    {
        return $canSkipRequiredOption && !$option->getRequired();
    }

    /**
     * Check the bundle product for availability of required options
     *
     * @param Product $bundleProduct
     * @return bool
     */
    private function hasRequiredOption($bundleProduct)
    {
        $collection = clone $this->getBundleOptions($bundleProduct);
        $collection->clear();

        return $collection->addFilter(Option::KEY_REQUIRED, 1)->getSize() > 0;
    }

    /**
     * Get bundle options
     *
     * @param Product $saleableItem
     * @return \Magento\Bundle\Model\ResourceModel\Option\Collection
     */
    private function getBundleOptions(Product $saleableItem)
    {
        return $saleableItem->getTypeInstance()->getOptionsCollection($saleableItem);
    }
}
