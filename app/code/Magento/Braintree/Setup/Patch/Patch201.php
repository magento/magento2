<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Braintree\Setup\Patch;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;


/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class Patch201
{


    /**
     * @param \Magento\Framework\DB\FieldDataConverterFactory $fieldDataConverterFactory
     */
    private $fieldDataConverterFactory;
    /**
     * @param \Magento\Framework\DB\Select\QueryModifierFactory $queryModifierFactory
     */
    private $queryModifierFactory;

    /**
     * @param \Magento\Framework\DB\FieldDataConverterFactory $fieldDataConverterFactory @param \Magento\Framework\DB\Select\QueryModifierFactory $queryModifierFactory
     */
    public function __construct(\Magento\Framework\DB\FieldDataConverterFactory $fieldDataConverterFactory,
                                \Magento\Framework\DB\Select\QueryModifierFactory $queryModifierFactory)
    {
        $this->fieldDataConverterFactory = $fieldDataConverterFactory;
        $this->queryModifierFactory = $queryModifierFactory;
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
        $this->convertSerializedDataToJson($setup);

    }

    private function convertSerializedDataToJson(ModuleDataSetupInterface $setup
    )
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
