<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Braintree\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

/**
 * Class \Magento\Braintree\Setup\UpgradeData
 *
 * @since 2.2.0
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var \Magento\Framework\DB\FieldDataConverterFactory
     * @since 2.2.0
     */
    private $fieldDataConverterFactory;

    /**
     * @var \Magento\Framework\DB\Select\QueryModifierFactory
     * @since 2.2.0
     */
    private $queryModifierFactory;

    /**
     * UpgradeData constructor.
     *
     * @param \Magento\Framework\DB\FieldDataConverterFactory $fieldDataConverterFactory
     * @param \Magento\Framework\DB\Select\QueryModifierFactory $queryModifierFactory
     * @since 2.2.0
     */
    public function __construct(
        \Magento\Framework\DB\FieldDataConverterFactory $fieldDataConverterFactory,
        \Magento\Framework\DB\Select\QueryModifierFactory $queryModifierFactory
    ) {
        $this->fieldDataConverterFactory = $fieldDataConverterFactory;
        $this->queryModifierFactory = $queryModifierFactory;
    }

    /**
     * Upgrades data for Braintree module
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     * @since 2.2.0
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '2.0.1', '<')) {
            $this->convertSerializedDataToJson($setup);
        }
    }

    /**
     * Upgrade data to version 2.0.1, converts row data in the core_config_data table that uses the path
     * payment/braintree/countrycreditcard from serialized to JSON
     *
     * @param ModuleDataSetupInterface $setup
     * @return void
     * @since 2.2.0
     */
    private function convertSerializedDataToJson(ModuleDataSetupInterface $setup)
    {
        $fieldDataConverter = $this->fieldDataConverterFactory->create(
            \Magento\Framework\DB\DataConverter\SerializedToJson::class
        );

        $queryModifier = $this->queryModifierFactory->create(
            'in',
            [
                'values' => [
                    'path' => ['payment/braintree/countrycreditcard']
                ]
            ]
        );

        $fieldDataConverter->convert(
            $setup->getConnection(),
            $setup->getTable('core_config_data'),
            'config_id',
            'value',
            $queryModifier
        );
    }
}
