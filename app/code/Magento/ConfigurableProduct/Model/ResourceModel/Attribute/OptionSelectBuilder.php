<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Model\ResourceModel\Attribute;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Attribute;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\EntityManager\MetadataPool;

/**
 * Build select object for retrieving configurable options.
 */
class OptionSelectBuilder implements OptionSelectBuilderInterface
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
     * @param Attribute $attributeResource
     * @param MetadataPool $metadataPool
     */
    public function __construct(Attribute $attributeResource, MetadataPool $metadataPool)
    {
        $this->attributeResource = $attributeResource;
        $this->metadataPool = $metadataPool;
    }

    /**
     * @inheritdoc
     */
    public function getSelect(AbstractAttribute $superAttribute, int $productId)
    {
        $productLinkField = $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField();

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
            ['product_entity' => $this->attributeResource->getTable('catalog_product_entity')],
            "product_entity.$productLinkField = super_attribute.product_id",
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
                    "entity_value.$productLinkField = entity.$productLinkField",
                ]
            ),
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

        if (!$superAttribute->getSourceModel()) {
            $select->joinLeft(
                ['option_value' => $this->attributeResource->getTable('eav_attribute_option_value')],
                implode(
                    ' AND ',
                    [
                        'option_value.option_id = entity_value.value',
                        'option_value.store_id = 0',
                    ]
                ),
                [
                    'option_title' => 'option_value.value',
                    'default_title' => 'option_value.value',
                ]
            );
        }

        return $select;
    }
}
