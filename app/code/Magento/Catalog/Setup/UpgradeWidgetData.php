<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Setup;

use Magento\Framework\DB\Select\QueryModifierFactory;
use Magento\Widget\Setup\LayoutUpdateConverter;
use Magento\Eav\Setup\EavSetup;
use Magento\Framework\DB\FieldToConvert;
use Magento\Framework\DB\AggregatedFieldDataConverter;

/**
 * Convert serialized widget data for categories and products tables to JSON
 */
class UpgradeWidgetData
{
    /**
     * @var EavSetup
     */
    private $eavSetup;

    /**
     * @var QueryModifierFactory
     */
    private $queryModifierFactory;

    /**
     * Constructor
     *
     * @param EavSetup $eavSetup
     * @param QueryModifierFactory $queryModifierFactory
     * @param AggregatedFieldDataConverter $aggregatedFieldDataConverter
     */
    public function __construct(
        EavSetup $eavSetup,
        QueryModifierFactory $queryModifierFactory,
        AggregatedFieldDataConverter $aggregatedFieldDataConverter
    ) {
        $this->eavSetup = $eavSetup;
        $this->queryModifierFactory = $queryModifierFactory;
        $this->aggregatedFieldDataConverter = $aggregatedFieldDataConverter;
    }

    /**
     * Convert category and product layout update
     *
     * @return void
     * @throws \InvalidArgumentException
     */
    public function upgrade()
    {
        $categoryTypeId = $this->eavSetup->getEntityTypeId(\Magento\Catalog\Model\Category::ENTITY);
        $categoryLayoutUpdateAttribute = $this->eavSetup->getAttribute($categoryTypeId, 'custom_layout_update');
        $categoryLayoutUpdateAttributeModifier = $this->queryModifierFactory->create(
            'in',
            [
                'values' => [
                    'attribute_id' => $categoryLayoutUpdateAttribute['attribute_id']
                ]
            ]
        );
        $layoutUpdateValueModifier = $this->queryModifierFactory->create(
            'like',
            [
                'values' => [
                    'value' => '%conditions_encoded%'
                ]
            ]
        );
        $categoryLayoutUpdateModifier = $this->queryModifierFactory->create(
            'composite',
            [
                'queryModifiers' => [
                    $categoryLayoutUpdateAttributeModifier,
                    $layoutUpdateValueModifier
                ]
            ]
        );
        $productTypeId = $this->eavSetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);
        $productLayoutUpdateAttribute = $this->eavSetup->getAttribute($productTypeId, 'custom_layout_update');
        $productLayoutUpdateAttributeModifier = $this->queryModifierFactory->create(
            'in',
            [
                'values' => [
                    'attribute_id' => $productLayoutUpdateAttribute['attribute_id']
                ]
            ]
        );
        $productLayoutUpdateModifier = $this->queryModifierFactory->create(
            'composite',
            [
                'queryModifiers' => [
                    $productLayoutUpdateAttributeModifier,
                    $layoutUpdateValueModifier
                ]
            ]
        );
        $this->aggregatedFieldDataConverter->convert(
            [
                new FieldToConvert(
                    LayoutUpdateConverter::class,
                    $this->eavSetup->getSetup()->getTable('catalog_category_entity_text'),
                    'value_id',
                    'value',
                    $categoryLayoutUpdateModifier
                ),
                new FieldToConvert(
                    LayoutUpdateConverter::class,
                    $this->eavSetup->getSetup()->getTable('catalog_product_entity_text'),
                    'value_id',
                    'value',
                    $productLayoutUpdateModifier
                ),
            ],
            $this->eavSetup->getSetup()->getConnection()
        );
    }
}
