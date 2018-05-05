<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\Product\Option\Repository as OptionRepository;

/**
 * Catalog product copier. Creates product duplicate
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
        $this->getOptionRepository()->duplicate($product, $duplicate);
        $product->getResource()->duplicate(
            $product->getData($metadata->getLinkField()),
            $duplicate->getData($metadata->getLinkField())
        );
        return $duplicate;
    }

    /**
     * @return OptionRepository
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
     * @return MetadataPool
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
