<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CurrencySymbol\Setup\Patch;

use Magento\CurrencySymbol\Model\System\Currencysymbol;
use Magento\Framework\DB\DataConverter\SerializedToJson;
use Magento\Framework\DB\FieldDataConverterFactory;
use Magento\Framework\DB\Select\QueryModifierFactory;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;


/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class Patch201
{


    /**
     * @param FieldDataConverterFactory $fieldDataConverterFactory
     */
    private $fieldDataConverterFactory;
    /**
     * @param QueryModifierFactory $queryModifierFactory
     */
    private $queryModifierFactory;

    /**
     * @param FieldDataConverterFactory $fieldDataConverterFactory @param QueryModifierFactory $queryModifierFactory
     */
    public function __construct(FieldDataConverterFactory $fieldDataConverterFactory,
                                QueryModifierFactory $queryModifierFactory)
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
        $this->convertSerializedCustomCurrencySymbolToJson($setup);

    }

    private function convertSerializedCustomCurrencySymbolToJson(ModuleDataSetupInterface $setup
    )
    {
        $fieldDataConverter = $this->fieldDataConverterFactory->create(SerializedToJson::class);
        $queryModifier = $this->queryModifierFactory->create(
            'in',
            [
                'values' => [
                    'path' => [Currencysymbol::XML_PATH_CUSTOM_CURRENCY_SYMBOL]
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
