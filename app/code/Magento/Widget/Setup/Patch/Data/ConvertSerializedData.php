<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Widget\Setup\Patch\Data;

use Magento\Framework\DB\AggregatedFieldDataConverter;
use Magento\Framework\DB\FieldToConvert;
use Magento\Framework\DB\Select\QueryModifierFactory;
use Magento\Framework\DB\DataConverter\SerializedToJson;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Widget\Setup\LayoutUpdateConverter;

/**
 * Class ConvertSerializedData
 * @package Magento\Widget\Setup\Patch
 */
class ConvertSerializedData implements DataPatchInterface, PatchVersionInterface
{
    /**
     * ConvertSerializedData constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param QueryModifierFactory $queryModifierFactory
     * @param AggregatedFieldDataConverter $aggregatedFieldDataConverter
     */
    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup,
        private readonly QueryModifierFactory $queryModifierFactory,
        private readonly AggregatedFieldDataConverter $aggregatedFieldDataConverter
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->convertSerializedData();
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [UpgradeModelInstanceClassAliases::class];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '2.0.1';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * Convert native serialized data to json.
     */
    private function convertSerializedData()
    {
        $layoutUpdateQueryModifier = $this->queryModifierFactory->create(
            'like',
            [
                'values' => [
                    'xml' => '%conditions_encoded%'
                ]
            ]
        );
        $this->aggregatedFieldDataConverter->convert(
            [
                new FieldToConvert(
                    SerializedToJson::class,
                    $this->moduleDataSetup->getTable('widget_instance'),
                    'instance_id',
                    'widget_parameters'
                ),
                new FieldToConvert(
                    LayoutUpdateConverter::class,
                    $this->moduleDataSetup->getTable('layout_update'),
                    'layout_update_id',
                    'xml',
                    $layoutUpdateQueryModifier
                ),
            ],
            $this->moduleDataSetup->getConnection()
        );
    }
}
