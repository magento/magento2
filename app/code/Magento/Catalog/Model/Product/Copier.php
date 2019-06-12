<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
<<<<<<< HEAD
use Magento\Framework\App\ObjectManager;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\Product\Option\Repository as OptionRepository;

/**
 * Catalog product copier. Creates product duplicate
=======

/**
 * Catalog product copier.
 *
 * Creates product duplicate.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
 */
class Copier
{
    /**
     * @var OptionRepository
     */
    protected $optionRepository;

    /**
     * @var CopyConstructorInterface
     */
    protected $copyConstructor;

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * @param CopyConstructorInterface $copyConstructor
     * @param ProductFactory $productFactory
     */
    public function __construct(
        CopyConstructorInterface $copyConstructor,
        ProductFactory $productFactory
    ) {
        $this->productFactory = $productFactory;
        $this->copyConstructor = $copyConstructor;
    }

    /**
     * Create product duplicate
     *
     * @param Product $product
     * @return Product
     */
    public function copy(Product $product)
    {
        $product->getWebsiteIds();
        $product->getCategoryIds();

        $metadata = $this->getMetadataPool()->getMetadata(ProductInterface::class);

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
<<<<<<< HEAD
        $isDuplicateSaved = false;
        do {
            $urlKey = $duplicate->getUrlKey();
            $urlKey = preg_match('/(.*)-(\d+)$/', $urlKey, $matches)
                ? $matches[1] . '-' . ($matches[2] + 1)
                : $urlKey . '-1';
            $duplicate->setUrlKey($urlKey);
            try {
                $duplicate->save();
                $isDuplicateSaved = true;
            } catch (\Magento\Framework\Exception\AlreadyExistsException $e) {
            } catch (\Magento\UrlRewrite\Model\Exception\UrlAlreadyExistsException $e) {
            }
        } while (!$isDuplicateSaved);
=======
        $this->setDefaultUrl($product, $duplicate);
        $this->setStoresUrl($product, $duplicate);
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
        $this->getOptionRepository()->duplicate($product, $duplicate);
        $product->getResource()->duplicate(
            $product->getData($metadata->getLinkField()),
            $duplicate->getData($metadata->getLinkField())
        );
        return $duplicate;
    }

    /**
<<<<<<< HEAD
     * @return OptionRepository
=======
     * Set default URL.
     *
     * @param Product $product
     * @param Product $duplicate
     * @return void
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
     * @return void
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
     * Returns product option repository.
     *
     * @return Option\Repository
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     * @deprecated 101.0.0
     */
    private function getOptionRepository()
    {
        if (null === $this->optionRepository) {
            $this->optionRepository = ObjectManager::getInstance()->get(OptionRepository::class);
        }
        return $this->optionRepository;
    }

    /**
<<<<<<< HEAD
     * @return MetadataPool
=======
     * Returns metadata pool.
     *
     * @return \Magento\Framework\EntityManager\MetadataPool
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     * @deprecated 101.0.0
     */
    private function getMetadataPool()
    {
        if (null === $this->metadataPool) {
            $this->metadataPool = ObjectManager::getInstance()->get(MetadataPool::class);
        }
        return $this->metadataPool;
    }

    /**
     * Remove stock item
     *
     * @param array $productData
     * @return array
     */
    private function removeStockItem(array $productData)
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
