<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Setup\Patch;

use Magento\Framework\DB\AggregatedFieldDataConverter;
use Magento\Framework\DB\DataConverter\SerializedToJson;
use Magento\Framework\DB\FieldToConvert;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\CatalogRule\Api\Data\RuleInterface;


/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class Patch203 implements \Magento\Setup\Model\Patch\DataPatchInterface
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
    public function apply(ModuleDataSetupInterface $setup)
    {
        $setup->startSetup();

        $this->convertSerializedDataToJson($setup);

        $setup->endSetup();

    }

    /**
     * Do Revert
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function revert(ModuleDataSetupInterface $setup)
    {
    }

    /**
     * @inheritdoc
     */
    public function isDisabled()
    {
        return false;
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
