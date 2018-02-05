<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Setup\Patch;


/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class Patch202
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
     * @param \Magento\Framework\EntityManager\MetadataPool $metadataPool @param \Magento\Framework\DB\AggregatedFieldDataConverter $aggregatedFieldConverter
     */
    public function __construct(\Magento\Framework\EntityManager\MetadataPool $metadataPool,
                                \Magento\Framework\DB\AggregatedFieldDataConverter $aggregatedFieldConverter)
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
        $metadata = $this->metadataPool->getMetadata(\Magento\SalesRule\Api\Data\RuleInterface::class);
        $this->aggregatedFieldConverter->convert(
            [
                new \Magento\Framework\DB\FieldToConvert(
                    \Magento\Framework\DB\DataConverter\SerializedToJson::class,
                    $setup->getTable('salesrule'),
                    $metadata->getLinkField(),
                    'conditions_serialized'
                ),
                new \Magento\Framework\DB\FieldToConvert(
                    \Magento\Framework\DB\DataConverter\SerializedToJson::class,
                    $setup->getTable('salesrule'),
                    $metadata->getLinkField(),
                    'actions_serialized'
                ),
            ],
            $setup->getConnection()
        );

    }
}
