<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Setup;

use Magento\Framework\DB\FieldDataConverterFactory;
use Magento\Framework\DB\DataConverter\SerializedToJson;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\SalesRule\Api\Data\RuleInterface;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var FieldDataConverterFactory
     */
    private $fieldDataConverterFactory;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * UpgradeData constructor.
     *
     * @param FieldDataConverterFactory $fieldDataConverterFactory
     * @param MetadataPool $metadataPool
     */
    public function __construct(
        FieldDataConverterFactory $fieldDataConverterFactory,
        MetadataPool $metadataPool
    ) {
        $this->fieldDataConverterFactory = $fieldDataConverterFactory;
        $this->metadataPool = $metadataPool;
    }

    /**
     * @inheritdoc
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '2.0.1', '<')) {
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
     */
    public function convertSerializedDataToJson($setup)
    {
        $fieldDataConverter = $this->fieldDataConverterFactory->create(SerializedToJson::class);
        $metadata = $this->metadataPool->getMetadata(RuleInterface::class);

        $fieldDataConverter->convert(
            $setup->getConnection(),
            $setup->getTable('salesrule'),
            $metadata->getLinkField(),
            'conditions_serialized'
        );
        $fieldDataConverter->convert(
            $setup->getConnection(),
            $setup->getTable('salesrule'),
            $metadata->getLinkField(),
            'actions_serialized'
        );
    }
}
