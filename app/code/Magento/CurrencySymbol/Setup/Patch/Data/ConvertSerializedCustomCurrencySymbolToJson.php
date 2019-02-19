<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CurrencySymbol\Setup\Patch\Data;

use Magento\CurrencySymbol\Model\System\Currencysymbol;
use Magento\Framework\DB\DataConverter\SerializedToJson;
use Magento\Framework\DB\FieldDataConverterFactory;
use Magento\Framework\DB\Select\QueryModifierFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

/**
 * Class ConvertSerializedCustomCurrencySymbolToJson
 * @package Magento\CurrencySymbol\Setup\Patch
 */
class ConvertSerializedCustomCurrencySymbolToJson implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @param FieldDataConverterFactory $fieldDataConverterFactory
     */
    private $fieldDataConverterFactory;

    /**
     * @param QueryModifierFactory $queryModifierFactory
     */
    private $queryModifierFactory;

    /**
     * @param FieldDataConverterFactory $fieldDataConverterFactory
     * @param QueryModifierFactory $queryModifierFactory
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        FieldDataConverterFactory $fieldDataConverterFactory,
        QueryModifierFactory $queryModifierFactory,
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->fieldDataConverterFactory = $fieldDataConverterFactory;
        $this->queryModifierFactory = $queryModifierFactory;
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
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
