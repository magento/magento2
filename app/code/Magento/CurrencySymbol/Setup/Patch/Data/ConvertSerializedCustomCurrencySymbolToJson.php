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
use Magento\Framework\App\ResourceConnection;
use Magento\Setup\Model\Patch\DataPatchInterface;
use Magento\Setup\Model\Patch\PatchVersionInterface;

/**
 * Class ConvertSerializedCustomCurrencySymbolToJson
 * @package Magento\CurrencySymbol\Setup\Patch
 */
class ConvertSerializedCustomCurrencySymbolToJson implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;
    
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
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        FieldDataConverterFactory $fieldDataConverterFactory,
        QueryModifierFactory $queryModifierFactory,
        ResourceConnection $resourceConnection

    ) {
        $this->fieldDataConverterFactory = $fieldDataConverterFactory;
        $this->queryModifierFactory = $queryModifierFactory;
        $this->resourceConnection = $resourceConnection;
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
            $this->resourceConnection->getConnection(),
            $this->resourceConnection->getConnection()->getTableName('core_config_data'),
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
    public function getVersion()
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
