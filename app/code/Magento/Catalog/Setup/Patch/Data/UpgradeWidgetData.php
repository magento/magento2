<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\DB\AggregatedFieldDataConverter;
use Magento\Framework\DB\FieldToConvert;
use Magento\Framework\DB\Select\QueryModifierFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Widget\Setup\LayoutUpdateConverter;

/**
 * Class UpgradeWidgetData.
 *
 * @package Magento\Catalog\Setup\Patch
 */
class UpgradeWidgetData implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var EavSetup
     */
    private $eavSetup;

    /**
     * @var QueryModifierFactory
     */
    private $queryModifierFactory;

    /**
     * @var AggregatedFieldDataConverter
     */
    private $aggregatedFieldDataConverter;

    /**
     * PrepareInitialConfig constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     * @param QueryModifierFactory $queryModifierFactory
     * @param AggregatedFieldDataConverter $aggregatedFieldDataConverter
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory,
        QueryModifierFactory $queryModifierFactory,
        AggregatedFieldDataConverter $aggregatedFieldDataConverter
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetup = $eavSetupFactory->create(['setup' => $moduleDataSetup]);
        $this->queryModifierFactory = $queryModifierFactory;
        $this->aggregatedFieldDataConverter = $aggregatedFieldDataConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
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

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [
            DisallowUsingHtmlForProductName::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '2.2.1';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
