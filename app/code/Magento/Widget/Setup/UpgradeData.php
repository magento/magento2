<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Widget\Setup;

use Magento\Framework\DB\AggregatedFieldDataConverter;
use Magento\Framework\DB\FieldToConvert;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\DB\DataConverter\SerializedToJson;

/**
 * Upgrade data for widget module.
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var \Magento\Framework\DB\Select\QueryModifierFactory
     */
    private $queryModifierFactory;

    /**
     * @var AggregatedFieldDataConverter
     */
    private $aggregatedFieldConverter;

    /**
     * UpgradeData constructor
     *
     * @param AggregatedFieldDataConverter $aggregatedFieldConverter
     * @param \Magento\Framework\DB\Select\QueryModifierFactory $queryModifierFactory
     */
    public function __construct(
        AggregatedFieldDataConverter $aggregatedFieldConverter,
        \Magento\Framework\DB\Select\QueryModifierFactory $queryModifierFactory
    ) {
        $this->aggregatedFieldConverter = $aggregatedFieldConverter;
        $this->queryModifierFactory = $queryModifierFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '2.0.1', '<')) {
            $this->upgradeVersionTwoZeroOne($setup);
        }
    }

    /**
     * Upgrade data to version 2.0.1
     *
     * @param ModuleDataSetupInterface $setup
     * @return void
     */
    private function upgradeVersionTwoZeroOne(ModuleDataSetupInterface $setup)
    {
        $layoutUpdateQueryModifier = $this->queryModifierFactory->create(
            'like',
            [
                'values' => [
                    'xml' => '%conditions_encoded%'
                ]
            ]
        );
        $this->aggregatedFieldConverter->convert(
            [
                new FieldToConvert(
                    SerializedToJson::class,
                    $setup->getTable('widget_instance'),
                    'instance_id',
                    'widget_parameters'
                ),
                new FieldToConvert(
                    LayoutUpdateConverter::class,
                    $setup->getTable('layout_update'),
                    'layout_update_id',
                    'xml',
                    $layoutUpdateQueryModifier
                ),
            ],
            $setup->getConnection()
        );
    }
}
