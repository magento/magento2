<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Widget\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\DB\FieldDataConverterFactory;
use Magento\Framework\DB\DataConverter\SerializedToJson;

/**
 * Upgrade data for widget module.
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var FieldDataConverterFactory
     */
    private $fieldDataConverterFactory;

    /**
     * @var \Magento\Framework\DB\Select\QueryModifierFactory
     */
    private $queryModifierFactory;
    
    /**
     * UpgradeData constructor
     * 
     * @param FieldDataConverterFactory $fieldDataConverterFactory
     * @param \Magento\Framework\DB\Select\QueryModifierFactory $queryModifierFactory
     */
    public function __construct(
        FieldDataConverterFactory $fieldDataConverterFactory,
        \Magento\Framework\DB\Select\QueryModifierFactory $queryModifierFactory
    ) {
        $this->fieldDataConverterFactory = $fieldDataConverterFactory;
        $this->queryModifierFactory = $queryModifierFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '2.0.1', '<')) {
            $this->upgradeVersionTwoZeroOne($setup);
        }
    }

    /**
     * Upgrade data to version 2.0.1
     *
     * @param ModuleDataSetupInterface $setup
     * @return void
     */
    private function upgradeVersionTwoZeroOne(ModuleDataSetupInterface $setup)
    {
        // upgrade widget_parameters field in widget_instance table
        $fieldDataConverter = $this->fieldDataConverterFactory->create(SerializedToJson::class);
        $fieldDataConverter->convert(
            $setup->getConnection(),
            $setup->getTable('widget_instance'),
            'instance_id',
            'widget_parameters'
        );

        // upgrade xml field in layout_update table if there is conditions in a widget instance
        $queryModifier = $this->queryModifierFactory->create(
            'like',
            [
                'values' => [
                    'xml' => '%conditions_encoded%'
                ]
            ]
        );

        $xmlFieldDataConverter = $this->fieldDataConverterFactory->create(LayoutUpdateConverter::class);
        $xmlFieldDataConverter->convert(
            $setup->getConnection(),
            $setup->getTable('layout_update'),
            'layout_update_id',
            'xml',
            $queryModifier
        );
    }
}
