<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Option\Repository;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\EntityManager\MetadataPool;

/**
 * Catalog product copier.
 *
 * Creates product duplicate.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Copier
{
    /**
     * @var CopyConstructorInterface
     */
    protected $copyConstructor;

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var Option\Repository
     */
    protected $optionRepository;

    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * @param CopyConstructorInterface $copyConstructor
     * @param ProductFactory $productFactory
     * @param ProductRepositoryInterface|null $productRepository
     * @param MetadataPool|null $metadataPool
     */
    public function __construct(
        CopyConstructorInterface $copyConstructor,
        ProductFactory $productFactory,
        Repository $optionRepository = null,
        MetadataPool $metadataPool = null
    ) {
        $this->productFactory = $productFactory;
        $this->copyConstructor = $copyConstructor;
        $this->optionRepository = $optionRepository ?: ObjectManager::getInstance()->get(Repository::class);
        $this->metadataPool = $metadataPool ?: ObjectManager::getInstance()->get(MetadataPool::class);
    }

    /**
     * Create product duplicate
     *
     * @param \Magento\Catalog\Model\Product|\Magento\Catalog\Api\Data\ProductInterface $product
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function copy(Product $product)
    {
        $product->getWebsiteIds();
        $product->getCategoryIds();

        /** @var \Magento\Framework\EntityManager\EntityMetadataInterface $metadata */
        $metadata = $this->metadataPool->getMetadata(ProductInterface::class);

        /** @var \Magento\Catalog\Model\Product $duplicate */
        $duplicate = $this->productFactory->create();
        $productData = $product->getData();
        $productData = $this->removeStockItem($productData);
        $duplicate->setData($productData);
        $duplicate->setOptions([]);
        $duplicate->setIsDuplicate(true);
        $duplicate->setOriginalLinkId($product->getData($metadata->getLinkField()));
        $duplicate->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED);
        $duplicate->setCreatedAt(null);
        $duplicate->setUpdatedAt(null);
        $duplicate->setId(null);
        $duplicate->setStoreId(\Magento\Store\Model\Store::DEFAULT_STORE_ID);
        $this->copyConstructor->build($product, $duplicate);
        $this->setDefaultUrl($product, $duplicate);
        $this->setStoresUrl($product, $duplicate);
        $this->optionRepository->duplicate($product, $duplicate);
        $product->getResource()->duplicate(
            $product->getData($metadata->getLinkField()),
            $duplicate->getData($metadata->getLinkField())
        );

        return $duplicate;
    }

    /**
     * Set default URL.
     *
     * @param Product $product
     * @param Product $duplicate
     *
     * @return void
     * @throws \Exception
     */
    private function setDefaultUrl(Product $product, Product $duplicate) : void
    {
        $duplicate->setStoreId(\Magento\Store\Model\Store::DEFAULT_STORE_ID);
        $resource = $product->getResource();
        $attribute = $resource->getAttribute('url_key');
        $productId = $product->getId();
        $urlKey = $resource->getAttributeRawValue($productId, 'url_key', \Magento\Store\Model\Store::DEFAULT_STORE_ID);
        do {
            $urlKey = $this->modifyUrl($urlKey);
            $duplicate->setUrlKey($urlKey);
        } while (!$attribute->getEntity()->checkAttributeUniqueValue($attribute, $duplicate));
        $duplicate->setData('url_path', null);
        $duplicate->save();
    }

    /**
     * Set URL for each store.
     *
     * @param Product $product
     * @param Product $duplicate
     *
     * @return void
     * @throws \Exception
     */
    private function setStoresUrl(Product $product, Product $duplicate) : void
    {
        $storeIds = $duplicate->getStoreIds();
        $productId = $product->getId();
        $productResource = $product->getResource();
        $defaultUrlKey = $productResource->getAttributeRawValue(
            $productId,
            'url_key',
            \Magento\Store\Model\Store::DEFAULT_STORE_ID
        );
        $duplicate->setData('save_rewrites_history', false);
        foreach ($storeIds as $storeId) {
            $isDuplicateSaved = false;
            $duplicate->setStoreId($storeId);
            $urlKey = $productResource->getAttributeRawValue($productId, 'url_key', $storeId);
            if ($urlKey === $defaultUrlKey) {
                continue;
            }
            do {
                $urlKey = $this->modifyUrl($urlKey);
                $duplicate->setUrlKey($urlKey);
                $duplicate->setData('url_path', null);
                try {
                    $duplicate->save();
                    $isDuplicateSaved = true;
                    // phpcs:ignore Magento2.CodeAnalysis.EmptyBlock
                } catch (\Magento\Framework\Exception\AlreadyExistsException $e) {
                }
            } while (!$isDuplicateSaved);
        }
        $duplicate->setStoreId(\Magento\Store\Model\Store::DEFAULT_STORE_ID);
    }

    /**
     * Modify URL key.
     *
     * @param string $urlKey
     * @return string
     */
    private function modifyUrl(string $urlKey) : string
    {
        return preg_match('/(.*)-(\d+)$/', $urlKey, $matches)
            ? $matches[1] . '-' . ($matches[2] + 1)
            : $urlKey . '-1';
    }

    /**
     * Remove stock item
     *
     * @param array $productData
     * @return array
     */
    private function removeStockItem(array $productData) : array
    {
        if (isset($productData[ProductInterface::EXTENSION_ATTRIBUTES_KEY])) {
            $extensionAttributes = $productData[ProductInterface::EXTENSION_ATTRIBUTES_KEY];
            if (null !== $extensionAttributes->getStockItem()) {
                $extensionAttributes->setData('stock_item', null);
            }
        }

        return $productData;
    }
}
