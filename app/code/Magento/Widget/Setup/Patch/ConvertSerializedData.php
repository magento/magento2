<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Widget\Setup\Patch;

use Magento\Framework\DB\AggregatedFieldDataConverter;
use Magento\Framework\DB\FieldToConvert;
use Magento\Framework\DB\Select\QueryModifierFactory;
use Magento\Framework\DB\DataConverter\SerializedToJson;
use Magento\Framework\App\ResourceConnection;
use Magento\Setup\Model\Patch\DataPatchInterface;
use Magento\Setup\Model\Patch\PatchVersionInterface;
use Magento\Widget\Setup\LayoutUpdateConverter;

/**
 * Class ConvertSerializedData
 * @package Magento\Widget\Setup\Patch
 */
class ConvertSerializedData implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var QueryModifierFactory
     */
    private $queryModifierFactory;

    /**
     * @var AggregatedFieldDataConverter
     */
    private $aggregatedFieldDataConverter;

    /**
     * ConvertSerializedData constructor.
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        QueryModifierFactory $queryModifierFactory,
        AggregatedFieldDataConverter $aggregatedFieldDataConverter
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->queryModifierFactory = $queryModifierFactory;
        $this->aggregatedFieldDataConverter = $aggregatedFieldDataConverter;
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
    public function getVersion()
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
                    $this->resourceConnection->getConnection()->getTableName('widget_instance'),
                    'instance_id',
                    'widget_parameters'
                ),
                new FieldToConvert(
                    LayoutUpdateConverter::class,
                    $this->resourceConnection->getConnection()->getTableName('layout_update'),
                    'layout_update_id',
                    'xml',
                    $layoutUpdateQueryModifier
                ),
            ],
            $this->resourceConnection->getConnection()
        );

    }
}
