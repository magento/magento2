<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event\ManagerInterface;
use Magento\GraphQl\Model\Query\ContextInterface;

/**
 * Processing applied to the collection after load
 */
class CollectionPostProcessor implements CollectionPostProcessorInterface
{
    private const PRICE_FIELDS = ['price', 'price_range', 'price_tiers'];

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @param ManagerInterface|null $eventManager
     */
    public function __construct(?ManagerInterface $eventManager = null)
    {
        $this->eventManager = $eventManager ?? ObjectManager::getInstance()->get(ManagerInterface::class);
    }

    /**
     * Apply processing to loaded product collection
     *
     * @param Collection $collection
     * @param array $attributeNames
     * @param ContextInterface|null $context
     * @return Collection
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function process(
        Collection $collection,
        array $attributeNames,
        ?ContextInterface $context = null
    ): Collection {
        if (!$collection->isLoaded()) {
            $collection->load();
        }
        // Methods that perform extra fetches post-load
        if (in_array('media_gallery_entries', $attributeNames)) {
            $collection->addMediaGalleryData();
        }
        if (in_array('media_gallery', $attributeNames)) {
            $collection->addMediaGalleryData();
        }
        if (in_array('options', $attributeNames)) {
            $collection->addOptionsToResult();
        }
        if (array_intersect(self::PRICE_FIELDS, $attributeNames) && $collection->count()) {
            $this->prepareCollectionPrices($collection);
        }

        return $collection;
    }

    /**
     * Load tier prices and catalog rule prices for the whole collection instead of once per product
     *
     * @param Collection $collection
     * @return void
     */
    private function prepareCollectionPrices(Collection $collection): void
    {
        $collection->addTierPriceData();
        $this->eventManager->dispatch(
            'prepare_catalog_product_collection_prices',
            ['collection' => $collection, 'store_id' => $collection->getStoreId()]
        );
    }
}
