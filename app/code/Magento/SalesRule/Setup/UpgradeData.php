<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Setup;

use Magento\Framework\DB\AggregatedFieldDataConverter;
use Magento\Framework\DB\DataConverter\SerializedToJson;
use Magento\Framework\DB\FieldToConvert;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\SalesRule\Api\Data\RuleInterface;

/**
 * Class \Magento\SalesRule\Setup\UpgradeData
 *
 * @since 2.2.0
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var MetadataPool
     * @since 2.2.0
     */
    private $metadataPool;

    /**
     * @var AggregatedFieldDataConverter
     * @since 2.2.0
     */
    private $aggregatedFieldConverter;

    /**
     * UpgradeData constructor.
     *
     * @param AggregatedFieldDataConverter $aggregatedFieldConverter
     * @param MetadataPool $metadataPool
     * @since 2.2.0
     */
    public function __construct(
        AggregatedFieldDataConverter $aggregatedFieldConverter,
        MetadataPool $metadataPool
    ) {
        $this->aggregatedFieldConverter = $aggregatedFieldConverter;
        $this->metadataPool = $metadataPool;
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '2.0.2', '<')) {
            $this->convertSerializedDataToJson($setup);
        }

        $setup->endSetup();
    }

    /**
     * Convert metadata from serialized to JSON format:
     *
     * @param ModuleDataSetupInterface $setup
     *
     * @return void
     * @since 2.2.0
     */
    public function convertSerializedDataToJson($setup)
    {
        $metadata = $this->metadataPool->getMetadata(RuleInterface::class);
        $this->aggregatedFieldConverter->convert(
            [
                new FieldToConvert(
                    SerializedToJson::class,
                    $setup->getTable('salesrule'),
                    $metadata->getLinkField(),
                    'conditions_serialized'
                ),
                new FieldToConvert(
                    SerializedToJson::class,
                    $setup->getTable('salesrule'),
                    $metadata->getLinkField(),
                    'actions_serialized'
                ),
            ],
            $setup->getConnection()
        );
    }
}
