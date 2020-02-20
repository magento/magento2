<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
     * @var \Magento\Bundle\Pricing\Price\BundleSelectionPrice[]
     */
    private $priceList;

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
        $this->priceList = [];

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

            if (!$useRegularPrice) {
                $selectionsCollection->addAttributeToSelect('special_price');
                $selectionsCollection->addAttributeToSelect('special_from_date');
                $selectionsCollection->addAttributeToSelect('special_to_date');
                $selectionsCollection->addAttributeToSelect('tax_class_id');
            }

            if (!$searchMin && $option->isMultiSelection()) {
                $this->addMaximumMultiSelectionPriceList($bundleProduct, $selectionsCollection, $useRegularPrice);
            } else {
                $this->addMiniMaxPriceList($bundleProduct, $selectionsCollection, $searchMin, $useRegularPrice);
            }
        }

        if ($shouldFindMinOption) {
            $this->processMinPriceForNonRequiredOptions();
        }

        return $this->priceList;
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
     * Add minimum or maximum price for option
     *
     * @param Product $bundleProduct
     * @param \Magento\Bundle\Model\ResourceModel\Selection\Collection $selectionsCollection
     * @param bool $searchMin
     * @param bool $useRegularPrice
     * @return void
     */
    private function addMiniMaxPriceList(Product $bundleProduct, $selectionsCollection, $searchMin, $useRegularPrice)
    {
        $selectionsCollection->addPriceFilter($bundleProduct, $searchMin, $useRegularPrice);
        $selectionsCollection->setPage(0, 1);

        $selection = $selectionsCollection->getFirstItem();

        if (!$selection->isEmpty()) {
            $this->priceList[] = $this->selectionFactory->create(
                $bundleProduct,
                $selection,
                $selection->getSelectionQty(),
                [
                    'useRegularPrice' => $useRegularPrice,
                ]
            );
        }
    }

    /**
     * Add maximum price for multi selection option
     *
     * @param Product $bundleProduct
     * @param \Magento\Bundle\Model\ResourceModel\Selection\Collection $selectionsCollection
     * @param bool $useRegularPrice
     * @return void
     */
    private function addMaximumMultiSelectionPriceList(Product $bundleProduct, $selectionsCollection, $useRegularPrice)
    {
        $selectionsCollection->addPriceData();

        foreach ($selectionsCollection as $selection) {
            $this->priceList[] =  $this->selectionFactory->create(
                $bundleProduct,
                $selection,
                $selection->getSelectionQty(),
                [
                    'useRegularPrice' => $useRegularPrice,
                ]
            );
        }
    }

    /**
     * @return void
     */
    private function processMinPriceForNonRequiredOptions()
    {
        $minPrice = null;
        $priceSelection = null;
        foreach ($this->priceList as $price) {
            $minPriceTmp = $price->getAmount()->getValue() * $price->getQuantity();
            if (!$minPrice || $minPriceTmp < $minPrice) {
                $minPrice = $minPriceTmp;
                $priceSelection = $price;
            }
        }
        $this->priceList = $priceSelection ? [$priceSelection] : [];
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
