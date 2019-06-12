<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model\ResourceModel;

use Magento\Catalog\Model\Indexer\Product\Price\DimensionCollectionFactory;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Eav\Model\Config;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Search\Request\IndexScopeResolverInterface as TableResolver;

/**
 * Elasticsearch index resource model
 * @api
 * @since 100.1.0
 */
class Index extends \Magento\AdvancedSearch\Model\ResourceModel\Index
{
    /**
     * @var ProductRepositoryInterface
     * @since 100.1.0
     */
    protected $productRepository;

    /**
     * @var CategoryRepositoryInterface
     * @since 100.1.0
     */
    protected $categoryRepository;

    /**
     * @var Config
     * @since 100.1.0
     */
    protected $eavConfig;

    /**
     * Index constructor.
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param MetadataPool $metadataPool
     * @param ProductRepositoryInterface $productRepository
     * @param CategoryRepositoryInterface $categoryRepository
     * @param Config $eavConfig
     * @param null $connectionName
     * @param TableResolver|null $tableResolver
     * @param DimensionCollectionFactory|null $dimensionCollectionFactory
     * @SuppressWarnings(Magento.TypeDuplication)
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        MetadataPool $metadataPool,
        ProductRepositoryInterface $productRepository,
        CategoryRepositoryInterface $categoryRepository,
        Config $eavConfig,
        $connectionName = null,
        TableResolver $tableResolver = null,
        DimensionCollectionFactory $dimensionCollectionFactory = null
    ) {
        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
        $this->eavConfig = $eavConfig;
        parent::__construct(
            $context,
            $storeManager,
            $metadataPool,
            $connectionName,
            $tableResolver,
            $dimensionCollectionFactory
        );
    }

    /**
     * Retrieve all attributes for given product ids
     *
     * @param int $productId
     * @param array $indexData
     * @return array
     * @since 100.1.0
     */
    public function getFullProductIndexData($productId, $indexData)
    {
        $productAttributes = [];
        $attributeCodes = $this->eavConfig->getEntityAttributeCodes(ProductAttributeInterface::ENTITY_TYPE_CODE);
        $product = $this->productRepository->getById($productId);
        foreach ($attributeCodes as $attributeCode) {
            $value = $product->getData($attributeCode);
            $attribute = $this->eavConfig->getAttribute(
                ProductAttributeInterface::ENTITY_TYPE_CODE,
                $attributeCode
            );
            $frontendInput = $attribute->getFrontendInput();
            if (in_array($attribute->getAttributeId(), array_keys($indexData))) {
                if (is_array($indexData[$attribute->getAttributeId()])) {
                    if (isset($indexData[$attribute->getAttributeId()][$productId])) {
                        $value = $indexData[$attribute->getAttributeId()][$productId];
                    } else {
                        $value = implode(' ', $indexData[$attribute->getAttributeId()]);
                    }
                } else {
                    $value = $indexData[$attribute->getAttributeId()];
                }
            }
            if ($value) {
                $productAttributes[$attributeCode] = $value;
                if ($frontendInput == 'select') {
                    foreach ($attribute->getOptions() as $option) {
                        if ($option->getValue() == $value) {
                            $productAttributes[$attributeCode . '_value'] = $option->getLabel();
                        }
                    }
                }
            }
        }
        return $productAttributes;
    }

    /**
     * Prepare full category index data for products.
     *
     * @param int $storeId
     * @param null|array $productIds
     * @return array
     * @since 100.1.0
     */
    public function getFullCategoryProductIndexData($storeId = null, $productIds = null)
    {
        $categoryPositions = $this->getCategoryProductIndexData($storeId, $productIds);
        $categoryData = [];

        foreach ($categoryPositions as $productId => $positions) {
            foreach ($positions as $categoryId => $position) {
                $category = $this->categoryRepository->get($categoryId, $storeId);
                $categoryName = $category->getName();
                $categoryData[$productId][] = [
                    'id' => $categoryId,
                    'name' => $categoryName,
                    'position' => $position
                ];
            }
        }
        return $categoryData;
    }
}
