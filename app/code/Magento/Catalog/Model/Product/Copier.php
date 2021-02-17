<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Attribute\ScopeOverriddenValue;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Option\Repository as OptionRepository;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Store\Model\Store;

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
     * @var Option\Repository
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
     * @var ScopeOverriddenValue
     */
    private $scopeOverriddenValue;

    /**
     * @param CopyConstructorInterface $copyConstructor
     * @param ProductFactory $productFactory
     * @param ScopeOverriddenValue $scopeOverriddenValue
     * @param OptionRepository|null $optionRepository
     * @param MetadataPool|null $metadataPool
     */
    public function __construct(
        CopyConstructorInterface $copyConstructor,
        ProductFactory $productFactory,
        ScopeOverriddenValue $scopeOverriddenValue,
        OptionRepository $optionRepository,
        MetadataPool $metadataPool
    ) {
        $this->productFactory = $productFactory;
        $this->copyConstructor = $copyConstructor;
        $this->scopeOverriddenValue = $scopeOverriddenValue;
        $this->optionRepository = $optionRepository;
        $this->metadataPool = $metadataPool;
    }

    /**
     * Create product duplicate
     *
     * @param Product $product
     * @param Product $duplicate
     * @return Product
     */
    public function copy(Product $product, Product $duplicate): Product
    {
        $product->getWebsiteIds();
        $product->getCategoryIds();

        $metadata = $this->metadataPool->getMetadata(ProductInterface::class);

        $productData = $product->getData();
        $productData = $this->removeStockItem($productData);
        $duplicate->addData($productData);
        $duplicate->setOptions([]);
        $duplicate->setMetaTitle(null);
        $duplicate->setMetaKeyword(null);
        $duplicate->setMetaDescription(null);
        $duplicate->setIsDuplicate(true);
        $duplicate->setOriginalLinkId($product->getData($metadata->getLinkField()));
        $duplicate->setStatus(Status::STATUS_DISABLED);
        $duplicate->setCreatedAt(null);
        $duplicate->setUpdatedAt(null);
        $duplicate->setId(null);
        $duplicate->setStoreId(Store::DEFAULT_STORE_ID);
        $this->copyConstructor->build($product, $duplicate);
        $duplicate->save();
        $this->optionRepository->duplicate($product, $duplicate);

        return $duplicate;
    }

    /**
     * Remove stock item
     *
     * @param array $productData
     * @return array
     */
    private function removeStockItem(array $productData): array
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
