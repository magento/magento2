<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Setup\Patch;

use Magento\CatalogRule\Api\Data\RuleInterface;
use Magento\Framework\DB\AggregatedFieldDataConverter;
use Magento\Framework\DB\DataConverter\SerializedToJson;
use Magento\Framework\DB\FieldToConvert;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;


/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class Patch203
{


    /**
     * @param MetadataPool $metadataPool
     */
    private $metadataPool;
    /**
     * @param AggregatedFieldDataConverter $aggregatedFieldConverter
     */
    private $aggregatedFieldConverter;

    /**
     * @param MetadataPool $metadataPool @param AggregatedFieldDataConverter $aggregatedFieldConverter
     */
    public function __construct(MetadataPool $metadataPool

        ,
                                AggregatedFieldDataConverter $aggregatedFieldConverter)
    {
        $this->metadataPool = $metadataPool;
        $this->aggregatedFieldConverter = $aggregatedFieldConverter;
    }

    /**
     * Do Upgrade
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function up(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $this->convertSerializedDataToJson($setup);

        $setup->endSetup();

    }

    private function convertSerializedDataToJson($setup
    )
    {
        $metadata = $this->metadataPool->getMetadata(RuleInterface::class);
        $this->aggregatedFieldConverter->convert(
            [
                new FieldToConvert(
                    SerializedToJson::class,
                    $setup->getTable('catalogrule'),
                    $metadata->getLinkField(),
                    'conditions_serialized'
                ),
                new FieldToConvert(
                    SerializedToJson::class,
                    $setup->getTable('catalogrule'),
                    $metadata->getLinkField(),
                    'actions_serialized'
                ),
            ],
            $setup->getConnection()
        );

    }
}
