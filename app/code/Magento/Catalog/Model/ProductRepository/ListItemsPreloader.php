<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\ProductRepository;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Gallery\ReadHandler as GalleryReadHandler;
use Magento\Catalog\Model\ResourceModel\Product\Gallery;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Loads website ids, tier prices and media gallery entries for a page of products with one query per data set
 */
class ListItemsPreloader
{
    /**
     * @param ResourceConnection $resourceConnection
     * @param MetadataPool $metadataPool
     * @param StoreManagerInterface $storeManager
     * @param Gallery $galleryResource
     */
    public function __construct(
        private readonly ResourceConnection $resourceConnection,
        private readonly MetadataPool $metadataPool,
        private readonly StoreManagerInterface $storeManager,
        private readonly Gallery $galleryResource
    ) {
    }

    /**
     * Preload data for the given products
     *
     * Website ids and tier prices are set on the products, where their readers look before querying.
     * Media gallery entries are returned as read extension arguments, keyed by product id.
     *
     * @param Product[] $products
     * @return array
     */
    public function execute(array $products): array
    {
        if (!$products) {
            return [];
        }
        $linkField = $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField();
        $this->preloadWebsiteIds($products);
        $this->preloadTierPrices($products, $linkField);

        return $this->loadMediaGalleryEntries($products, $linkField);
    }

    /**
     * Set the website ids that Website\ReadHandler would otherwise load per product
     *
     * @param Product[] $products
     * @return void
     */
    private function preloadWebsiteIds(array $products): void
    {
        $productIds = [];
        foreach ($products as $product) {
            if ($product->getExtensionAttributes()->getWebsiteIds() === null) {
                $productIds[] = (int)$product->getId();
            }
        }
        if (!$productIds) {
            return;
        }

        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()
            ->from($this->resourceConnection->getTableName('catalog_product_website'), ['product_id', 'website_id'])
            ->where('product_id IN (?)', $productIds);
        $websiteIds = array_fill_keys($productIds, []);
        foreach ($connection->fetchAll($select) as $row) {
            $websiteIds[$row['product_id']][] = $row['website_id'];
        }

        foreach ($products as $product) {
            $extensionAttributes = $product->getExtensionAttributes();
            if ($extensionAttributes->getWebsiteIds() === null) {
                $extensionAttributes->setWebsiteIds($websiteIds[(int)$product->getId()]);
                $product->setExtensionAttributes($extensionAttributes);
            }
        }
    }

    /**
     * Set the tier price data that the tier price backend afterLoad() would otherwise load per product
     *
     * @param Product[] $products
     * @param string $linkField
     * @return void
     */
    private function preloadTierPrices(array $products, string $linkField): void
    {
        $attribute = reset($products)->getResource()->getAttribute('tier_price');
        if (!$attribute) {
            return;
        }

        $productsByWebsite = [];
        foreach ($products as $product) {
            if ($product->getData('tier_price') === null) {
                $websiteId = $this->getTierPriceWebsiteId($attribute, $product->getStoreId());
                $productsByWebsite[$websiteId ?? ''][] = $product;
            }
        }

        $backend = $attribute->getBackend();
        $priceResource = $backend->getResource();
        foreach ($productsByWebsite as $websiteId => $websiteProducts) {
            $select = $priceResource->getSelect($websiteId === '' ? null : $websiteId)
                ->where($linkField . ' IN (?)', $this->getLinkIds($websiteProducts, $linkField))
                ->order('qty');
            $tierPrices = [];
            foreach ($priceResource->getConnection()->fetchAll($select) as $row) {
                $tierPrices[$row['product_id']][] = $row;
            }
            foreach ($websiteProducts as $product) {
                $backend->setPriceData($product, $tierPrices[$product->getData($linkField)] ?? []);
            }
        }
    }

    /**
     * Resolve the website filter the same way the tier price backend does for a single product
     *
     * @param AbstractAttribute $attribute
     * @param int|string|null $storeId
     * @return int|null
     */
    private function getTierPriceWebsiteId(AbstractAttribute $attribute, $storeId): ?int
    {
        if ($attribute->isScopeGlobal()) {
            return 0;
        }

        return $storeId ? (int)$this->storeManager->getStore($storeId)->getWebsiteId() : null;
    }

    /**
     * Load the rows Gallery\ReadHandler would otherwise load per product
     *
     * A product whose rows repeat a file is left out, so the read handler loads it itself and removes the duplicates.
     *
     * @param Product[] $products
     * @param string $linkField
     * @return array
     */
    private function loadMediaGalleryEntries(array $products, string $linkField): array
    {
        $attribute = reset($products)->getResource()->getAttribute('media_gallery');
        if (!$attribute) {
            return [];
        }

        $productsByStore = [];
        foreach ($products as $product) {
            $productsByStore[(int)$product->getStoreId()][] = $product;
        }

        $arguments = [];
        foreach ($productsByStore as $storeId => $storeProducts) {
            $select = $this->galleryResource->createBatchBaseSelect($storeId, $attribute->getAttributeId())
                ->where('entity.' . $linkField . ' IN (?)', $this->getLinkIds($storeProducts, $linkField));
            $entries = [];
            foreach ($this->galleryResource->getConnection()->fetchAll($select) as $row) {
                $entries[$row[$linkField]][] = $row;
            }
            foreach ($storeProducts as $product) {
                $productEntries = $entries[$product->getData($linkField)] ?? [];
                if (count(array_unique(array_column($productEntries, 'file'))) === count($productEntries)) {
                    $arguments[$product->getId()][GalleryReadHandler::PRELOADED_ENTRIES] = $productEntries;
                }
            }
        }

        return $arguments;
    }

    /**
     * Collect link field values of the products
     *
     * @param Product[] $products
     * @param string $linkField
     * @return array
     */
    private function getLinkIds(array $products, string $linkField): array
    {
        $linkIds = [];
        foreach ($products as $product) {
            $linkIds[] = $product->getData($linkField);
        }

        return $linkIds;
    }
}
