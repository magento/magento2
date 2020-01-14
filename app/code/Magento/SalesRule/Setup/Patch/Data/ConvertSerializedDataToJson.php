<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

/**
 * Class ConvertSerializedDataToJson
 *
 * @package Magento\SalesRule\Setup\Patch
 */
class ConvertSerializedDataToJson implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @param \Magento\Framework\EntityManager\MetadataPool $metadataPool
     */
    private $metadataPool;

    /**
     * @param \Magento\Framework\DB\AggregatedFieldDataConverter $aggregatedFieldConverter
     */
    private $aggregatedFieldConverter;

    /**
     * @var \Magento\Framework\Setup\ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @param \Magento\Framework\EntityManager\MetadataPool $metadataPool
     * @param \Magento\Framework\DB\AggregatedFieldDataConverter $aggregatedFieldConverter
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        \Magento\Framework\EntityManager\MetadataPool $metadataPool,
        \Magento\Framework\DB\AggregatedFieldDataConverter $aggregatedFieldConverter,
        \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->metadataPool = $metadataPool;
        $this->aggregatedFieldConverter = $aggregatedFieldConverter;
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * Do Upgrade
     *
     * @return void
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $this->convertSerializedDataToJson();
        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [
            PrepareRuleModelSerializedData::class
        ];
    }

    /**
     * @inheritdoc
     */
    public static function getVersion()
    {
        return '2.0.2';
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * Convert native serialized data to json.
     *
     * @return void
     */
    private function convertSerializedDataToJson()
    {
        $metadata = $this->metadataPool->getMetadata(\Magento\SalesRule\Api\Data\RuleInterface::class);
        $this->aggregatedFieldConverter->convert(
            [
                new \Magento\Framework\DB\FieldToConvert(
                    \Magento\Framework\DB\DataConverter\SerializedToJson::class,
                    $this->moduleDataSetup->getTable('salesrule'),
                    $metadata->getLinkField(),
                    'conditions_serialized'
                ),
                new \Magento\Framework\DB\FieldToConvert(
                    \Magento\Framework\DB\DataConverter\SerializedToJson::class,
                    $this->moduleDataSetup->getTable('salesrule'),
                    $metadata->getLinkField(),
                    'actions_serialized'
                ),
            ],
            $this->moduleDataSetup->getConnection()
        );
    }
}
