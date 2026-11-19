<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Plugin\Catalog\Block\Product;

use Magento\Catalog\Block\Product\ListProduct;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\ConfigurableProduct\Pricing\Price\LowestPriceOptionsProvider;
use Magento\ConfigurableProduct\Pricing\Price\LowestPriceOptionsProviderInterface;
use WeakMap;

/**
 * Register the configurable products of a rendered listing with the lowest price options provider
 */
class PreloadLowestPriceOptions
{
    /**
     * @var WeakMap<ListProduct, Collection>
     */
    private WeakMap $blockCollections;

    /**
     * @var WeakMap<Collection, bool>
     */
    private WeakMap $registeredCollections;

    /**
     * @param LowestPriceOptionsProviderInterface $lowestPriceOptionsProvider
     */
    public function __construct(
        private readonly LowestPriceOptionsProviderInterface $lowestPriceOptionsProvider
    ) {
        $this->blockCollections = new WeakMap();
        $this->registeredCollections = new WeakMap();
    }

    /**
     * Remember the collection the listing renders, without initializing it
     *
     * @param ListProduct $subject
     * @param Collection $result
     * @return Collection
     */
    public function afterGetLoadedProductCollection(ListProduct $subject, $result)
    {
        if ($result instanceof Collection) {
            $this->blockCollections[$subject] = $result;
        }

        return $result;
    }

    /**
     * Register the loaded collection once, when the first configurable product of it is priced
     *
     * @param ListProduct $subject
     * @param Product $product
     * @return null
     */
    public function beforeGetProductPrice(ListProduct $subject, Product $product)
    {
        $collection = $this->blockCollections[$subject] ?? null;
        if ($collection === null
            || isset($this->registeredCollections[$collection])
            || $product->getTypeId() !== Configurable::TYPE_CODE
            || !$this->lowestPriceOptionsProvider instanceof LowestPriceOptionsProvider
            || !$collection->isLoaded()
            || $collection->getItemById($product->getId()) === null
        ) {
            return null;
        }
        $this->registeredCollections[$collection] = true;
        $this->lowestPriceOptionsProvider->preloadProducts($collection->getItems());

        return null;
    }
}
