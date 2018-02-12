<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Setup\Patch\Data;

use Magento\Framework\App\ResourceConnection;
use Magento\Setup\Model\Patch\DataPatchInterface;
use Magento\Setup\Model\Patch\PatchVersionInterface;

/**
 * Class ConvertSerializedDataToJson
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
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param \Magento\Framework\EntityManager\MetadataPool $metadataPool
     * @param \Magento\Framework\DB\AggregatedFieldDataConverter $aggregatedFieldConverter
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        \Magento\Framework\EntityManager\MetadataPool $metadataPool,
        \Magento\Framework\DB\AggregatedFieldDataConverter $aggregatedFieldConverter,
        ResourceConnection $resourceConnection
    ) {
        $this->metadataPool = $metadataPool;
        $this->aggregatedFieldConverter = $aggregatedFieldConverter;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Do Upgrade
     *
     * @return void
     */
    public function apply()
    {
        $this->resourceConnection->getConnection()->startSetup();
        $this->convertSerializedDataToJson();
        $this->resourceConnection->getConnection()->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [
            PrepareRuleModelSerializedData::class
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getVersion()
    {
        return '2.0.2';
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
                    $this->resourceConnection->getConnection()->getTableName('salesrule'),
                    $metadata->getLinkField(),
                    'conditions_serialized'
                ),
                new \Magento\Framework\DB\FieldToConvert(
                    \Magento\Framework\DB\DataConverter\SerializedToJson::class,
                    $this->resourceConnection->getConnection()->getTableName('salesrule'),
                    $metadata->getLinkField(),
                    'actions_serialized'
                ),
            ],
            $this->resourceConnection->getConnection()
        );
    }
}
