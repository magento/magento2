<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Model\ResourceModel\Attribute;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Model\Stock\Status as StockStatus;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Attribute;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Build select object for retrieving configurable options considering scope.
 */
class ScopedOptionSelectBuilder implements OptionSelectBuilderInterface
{
    /**
     * @var Attribute
     */
    private $attributeResource;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var StockConfigurationInterface
     */
    private $stockConfig;

    /**
     * @param Attribute $attributeResource
     * @param MetadataPool $metadataPool
     * @param StoreManagerInterface $storeManager
     * @param StockConfigurationInterface $stockConfig
     */
    public function __construct(
        Attribute $attributeResource,
        MetadataPool $metadataPool,
        StoreManagerInterface $storeManager,
        StockConfigurationInterface $stockConfig
    ) {
        $this->attributeResource = $attributeResource;
        $this->metadataPool = $metadataPool;
        $this->storeManager = $storeManager;
        $this->stockConfig = $stockConfig;
    }

    /**
     * @inheritdoc
     */
    public function getSelect(AbstractAttribute $superAttribute, int $productId)
    {
        $productMetadata = $this->metadataPool->getMetadata(ProductInterface::class);
        $store = $this->storeManager->getStore();

        $select = $this->attributeResource->getConnection()->select()->from(
            ['super_attribute' => $this->attributeResource->getTable('catalog_product_super_attribute')],
            [
                'sku' => 'entity.sku',
                'product_id' => 'product_entity.entity_id',
                'attribute_code' => 'attribute.attribute_code',
                'value_index' => 'entity_value.value',
                'super_attribute_label' => 'attribute_label.value',
            ]
        )->joinInner(
            ['product_link' => $this->attributeResource->getTable('catalog_product_super_link')],
            'product_link.parent_id = super_attribute.product_id',
            []
        )->joinInner(
            ['product_entity' => $this->attributeResource->getTable('catalog_product_entity')],
            "product_entity.{$productMetadata->getLinkField()} = super_attribute.product_id",
            []
        )->joinInner(
            ['attribute' => $this->attributeResource->getTable('eav_attribute')],
            'attribute.attribute_id = super_attribute.attribute_id',
            []
        )->joinLeft(
            ['attribute_label' => $this->attributeResource->getTable('catalog_product_super_attribute_label')],
            implode(
                ' AND ',
                [
                    'super_attribute.product_super_attribute_id = attribute_label.product_super_attribute_id',
                    'attribute_label.store_id = 0',
                ]
            ),
            []
        )->joinInner(
            ['entity' => $this->attributeResource->getTable('catalog_product_entity')],
            'entity.entity_id = product_link.product_id',
            []
        )->joinInner(
            ['entity_website' => $this->attributeResource->getTable('catalog_product_website')],
            implode(
                ' AND ',
                [
                    "entity_website.product_id = entity.{$productMetadata->getIdentifierField()}",
                    "entity_website.website_id = {$store->getWebsiteId()}",
                ]
            ),
            []
        )->joinInner(
            ['entity_value' => $superAttribute->getBackendTable()],
            implode(
                ' AND ',
                [
                    'entity_value.attribute_id = super_attribute.attribute_id',
                    'entity_value.store_id = 0',
                    "entity_value.{$productMetadata->getLinkField()} = entity.{$productMetadata->getLinkField()}",
                ]
            ),
            []
        )->joinLeft(
            ['attribute_option' => $this->attributeResource->getTable('eav_attribute_option')],
            'attribute_option.option_id = entity_value.value',
            []
        )->order(
            'attribute_option.sort_order ASC'
        )->where(
            'super_attribute.product_id = ?',
            $productId
        )->where(
            'attribute.attribute_id = ?',
            $superAttribute->getAttributeId()
        );

        if (!$this->stockConfig->isShowOutOfStock()) {
            $select->joinInner(
                ['stock' => $this->attributeResource->getTable('cataloginventory_stock_status')],
                'stock.product_id = entity.entity_id',
                []
            )->where(
                'stock.stock_status = ?',
                StockStatus::STATUS_IN_STOCK
            );
        }

        if (!$superAttribute->getSourceModel()) {
            $select->columns(
                [
                    'option_title' => $this->attributeResource->getConnection()->getIfNullSql(
                        'option_value.value',
                        'default_option_value.value'
                    ),
                    'default_title' => 'default_option_value.value',
                ]
            )->joinLeft(
                ['option_value' => $this->attributeResource->getTable('eav_attribute_option_value')],
                implode(
                    ' AND ',
                    [
                        'option_value.option_id = entity_value.value',
                        'option_value.store_id = ' . $store->getId(),
                    ]
                ),
                []
            )->joinLeft(
                ['default_option_value' => $this->attributeResource->getTable('eav_attribute_option_value')],
                implode(
                    ' AND ',
                    [
                        'default_option_value.option_id = entity_value.value',
                        'default_option_value.store_id = 0',
                    ]
                ),
                []
            );
        }

        return $select;
    }
}
