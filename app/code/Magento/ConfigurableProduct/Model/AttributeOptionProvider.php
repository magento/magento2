<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Model;

use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\App\ObjectManager;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Attribute;
use Magento\Framework\App\ScopeInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\CatalogInventory\Api\StockStatusRepositoryInterface;
use Magento\CatalogInventory\Api\StockStatusCriteriaInterfaceFactory;

class AttributeOptionProvider implements AttributeOptionProviderInterface
{
    /**
     * @var ScopeResolverInterface
     */
    private $scopeResolver;

    /**
     * @var Attribute
     */
    private $attributeResource;

    /**
     * Product entity link field
     *
     * @var string
     */
    private $productEntityLinkField;

    /**
     * Product metadata pool
     *
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var StockStatusRepositoryInterface
     */
    private $stockStatusRepository;

    /**
     * @var StockStatusCriteriaInterfaceFactory
     */
    private $stockStatusCriteriaFactory;

    /**
     * @param Attribute $attributeResource
     * @param StockStatusRepositoryInterface $stockStatusRepository
     * @param StockStatusCriteriaInterfaceFactory $stockStatusCriteriaFactory
     * @param ScopeResolverInterface $scopeResolver
     * @param MetadataPool $metadataPool
     */
    public function __construct(
        Attribute $attributeResource,
        StockStatusRepositoryInterface $stockStatusRepository,
        StockStatusCriteriaInterfaceFactory $stockStatusCriteriaFactory,
        ScopeResolverInterface $scopeResolver = null,
        MetadataPool $metadataPool = null
    ) {
        $this->attributeResource = $attributeResource;
        $this->stockStatusRepository = $stockStatusRepository;
        $this->stockStatusCriteriaFactory = $stockStatusCriteriaFactory;
        $this->scopeResolver = $scopeResolver ?: ObjectManager::getInstance()->get(ScopeResolverInterface::class);
        $this->metadataPool = $metadataPool ?: ObjectManager::getInstance()->get(MetadataPool::class);
    }

    /**
     * Retrieve options for attribute
     *
     * @param AbstractAttribute $superAttribute
     * @param int $productId
     * @return array
     */
    public function getAttributeOptions(AbstractAttribute $superAttribute, $productId)
    {
        $scope  = $this->scopeResolver->getScope();
        $select = $this->getAttributeOptionsSelect($superAttribute, $productId, $scope);

        return $this->attributeResource->getConnection()->fetchAll($select);
    }

    /**
     * Retrieve in stock options for attribute
     *
     * @param AbstractAttribute $superAttribute
     * @param int $productId
     * @return array
     */
    public function getInStockAttributeOptions(AbstractAttribute $superAttribute, $productId)
    {
        $scope  = $this->scopeResolver->getScope();
        $select = $this->getAttributeOptionsSelect($superAttribute, $productId, $scope);
        $options = $this->attributeResource->getConnection()->fetchAll($select);

        $sku = [];
        foreach ($options as $option) {
            $sku[] = $option['sku'];
        }
        $criteria = $this->stockStatusCriteriaFactory->create();
        $criteria->addFilter('stock_status', 'stock_status', '1');
        $criteria->addFilter('sku', 'sku', ['in' => $sku], 'public');
        $collection = $this->stockStatusRepository->getList($criteria);

        $inStockSku = [];
        foreach ($collection->getItems() as $inStockOption) {
            $inStockSku[] = $inStockOption->getData('sku');
        }
        foreach ($options as $key => $option) {
            if (!in_array($options[$key]['sku'], $inStockSku)) {
                unset($options[$key]);
            }
        }
        $options = array_values($options);

        return $options;
    }

    /**
     * Get load options for attribute select
     *
     * @param AbstractAttribute $superAttribute
     * @param int $productId
     * @param ScopeInterface $scope
     * @return Select
     */
    private function getAttributeOptionsSelect(AbstractAttribute $superAttribute, $productId, ScopeInterface $scope)
    {
        $select = $this->attributeResource->getConnection()->select()->from(
            ['super_attribute' => $this->attributeResource->getTable('catalog_product_super_attribute')],
            [
                'sku' => 'entity.sku',
                'product_id' => 'product_entity.entity_id',
                'attribute_code' => 'attribute.attribute_code',
                'value_index' => 'entity_value.value',
                'option_title' => $this->attributeResource->getConnection()->getIfNullSql(
                    'option_value.value',
                    'default_option_value.value'
                ),
                'default_title' => 'default_option_value.value',
            ]
        )->joinInner(
            ['product_entity' => $this->attributeResource->getTable('catalog_product_entity')],
            "product_entity.{$this->getProductEntityLinkField()} = super_attribute.product_id",
            []
        )->joinInner(
            ['product_link' => $this->attributeResource->getTable('catalog_product_super_link')],
            'product_link.parent_id = super_attribute.product_id',
            []
        )->joinInner(
            ['attribute' => $this->attributeResource->getTable('eav_attribute')],
            'attribute.attribute_id = super_attribute.attribute_id',
            []
        )->joinInner(
            ['entity' => $this->attributeResource->getTable('catalog_product_entity')],
            'entity.entity_id = product_link.product_id',
            []
        )->joinInner(
            ['entity_value' => $superAttribute->getBackendTable()],
            implode(
                ' AND ',
                [
                    'entity_value.attribute_id = super_attribute.attribute_id',
                    'entity_value.store_id = 0',
                    "entity_value.{$this->getProductEntityLinkField()} = "
                    . "entity.{$this->getProductEntityLinkField()}"
                ]
            ),
            []
        )->joinLeft(
            ['option_value' => $this->attributeResource->getTable('eav_attribute_option_value')],
            implode(
                ' AND ',
                [
                    'option_value.option_id = entity_value.value',
                    'option_value.store_id = ' . $scope->getId()
                ]
            ),
            []
        )->joinLeft(
            ['default_option_value' => $this->attributeResource->getTable('eav_attribute_option_value')],
            implode(
                ' AND ',
                [
                    'default_option_value.option_id = entity_value.value',
                    'default_option_value.store_id = ' . \Magento\Store\Model\Store::DEFAULT_STORE_ID
                ]
            ),
            []
        )->where(
            'super_attribute.product_id = ?',
            $productId
        )->where(
            'attribute.attribute_id = ?',
            $superAttribute->getAttributeId()
        );

        return $select;
    }

    /**
     * Get product entity link field
     *
     * @deprecated
     * @return string
     */
    public function getProductEntityLinkField()
    {
        if (!$this->productEntityLinkField) {
            $this->productEntityLinkField = $this->metadataPool
                ->getMetadata(ProductInterface::class)
                ->getLinkField();
        }
        return $this->productEntityLinkField;
    }
}
