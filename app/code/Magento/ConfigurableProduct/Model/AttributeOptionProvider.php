<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Model;

use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Attribute;
use Magento\Framework\App\ScopeInterface;
use Magento\Framework\DB\Select;
use Magento\ConfigurableProduct\Model\ResourceModel\Attribute\OptionProvider;

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
     * @var OptionProvider
     */
    private $attributeOptionProvider;

    /**
     * @param Attribute $attributeResource
     * @param OptionProvider $attributeOptionProvider,
     * @param ScopeResolverInterface $scopeResolver
     */
    public function __construct(
        Attribute $attributeResource,
        OptionProvider $attributeOptionProvider,
        ScopeResolverInterface $scopeResolver
    ) {
        $this->attributeResource = $attributeResource;
        $this->attributeOptionProvider = $attributeOptionProvider;
        $this->scopeResolver = $scopeResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeOptions(AbstractAttribute $superAttribute, $productId)
    {
        $scope  = $this->scopeResolver->getScope();
        $select = $this->getAttributeOptionsSelect($superAttribute, $productId, $scope);

        return $this->attributeResource->getConnection()->fetchAll($select);
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
                'super_attribute_label' => 'attribute_label.value',
            ]
        )->joinInner(
            ['product_entity' => $this->attributeResource->getTable('catalog_product_entity')],
            "product_entity.{$this->attributeOptionProvider->getProductEntityLinkField()} = super_attribute.product_id",
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
                    "entity_value.{$this->attributeOptionProvider->getProductEntityLinkField()} = "
                    . "entity.{$this->attributeOptionProvider->getProductEntityLinkField()}"
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
        )->joinLeft(
            ['attribute_label' => $this->attributeResource->getTable('catalog_product_super_attribute_label')],
            implode(
                ' AND ',
                [
                    'super_attribute.product_super_attribute_id = attribute_label.product_super_attribute_id',
                    'attribute_label.store_id = ' . \Magento\Store\Model\Store::DEFAULT_STORE_ID
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
}
