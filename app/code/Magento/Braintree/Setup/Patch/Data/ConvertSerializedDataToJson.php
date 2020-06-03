<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Braintree\Setup\Patch\Data;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

/**
 * Convert data from php native serialized data to JSON.
 *
 * @deprecated Starting from Magento 2.3.6 Braintree payment method core integration is deprecated
 * in favor of official payment integration available on the marketplace
 */
class ConvertSerializedDataToJson implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var \Magento\Framework\DB\FieldDataConverterFactory
     */
    private $fieldDataConverterFactory;

    /**
     * @var \Magento\Framework\DB\Select\QueryModifierFactory
     */
    private $queryModifierFactory;

    /**
     * ConvertSerializedDataToJson constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param \Magento\Framework\DB\FieldDataConverterFactory $fieldDataConverterFactory
     * @param \Magento\Framework\DB\Select\QueryModifierFactory $queryModifierFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        \Magento\Framework\DB\FieldDataConverterFactory $fieldDataConverterFactory,
        \Magento\Framework\DB\Select\QueryModifierFactory $queryModifierFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->fieldDataConverterFactory = $fieldDataConverterFactory;
        $this->queryModifierFactory = $queryModifierFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->convertSerializedDataToJson();
    }

    /**
     * Upgrade data to version 2.0.1, converts row data in the core_config_data table that uses the path
     * payment/braintree/countrycreditcard from serialized to JSON
     *
     * @return void
     */
    private function convertSerializedDataToJson()
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
            $this->moduleDataSetup->getConnection(),
            $this->moduleDataSetup->getTable('core_config_data'),
            'config_id',
            'value',
            $queryModifier
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '2.0.1';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
