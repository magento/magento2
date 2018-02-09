<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Setup\Patch;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Setup\Model\Patch\DataPatchInterface;
use Magento\Setup\Model\Patch\VersionedDataPatch;
use Magento\Framework\DB\AggregatedFieldDataConverter;
use Magento\Framework\DB\DataConverter\SerializedToJson;
use Magento\Framework\DB\FieldToConvert;
use Magento\CatalogRule\Api\Data\RuleInterface;


class ConvertSerializedDataToJson implements DataPatchInterface, VersionedDataPatch
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var AggregatedFieldDataConverter
     */
    private $aggregatedFieldDataConverter;

    /**
     * ConvertSerializedDataToJson constructor.
     * @param ResourceConnection $resourceConnection
     * @param MetadataPool $metadataPool
     * @param AggregatedFieldDataConverter $aggregatedFieldDataConverter
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        MetadataPool $metadataPool,
        AggregatedFieldDataConverter $aggregatedFieldDataConverter
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->metadataPool = $metadataPool;
        $this->aggregatedFieldDataConverter = $aggregatedFieldDataConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $metadata = $this->metadataPool->getMetadata(RuleInterface::class);
        $this->aggregatedFieldDataConverter->convert(
            [
                new FieldToConvert(
                    SerializedToJson::class,
                    $this->resourceConnection->getConnection()->getTableName('catalogrule'),
                    $metadata->getLinkField(),
                    'conditions_serialized'
                ),
                new FieldToConvert(
                    SerializedToJson::class,
                    $this->resourceConnection->getConnection()->getTableName('catalogrule'),
                    $metadata->getLinkField(),
                    'actions_serialized'
                ),
            ],
            $this->resourceConnection->getConnection()
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [
            UpdateClassAliasesForCatalogRules::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getVersion()
    {
        return '2.0.3';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
