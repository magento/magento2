<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Setup\Patch\Data;

use Magento\Framework\DB\FieldDataConverterFactory;
use Magento\Framework\DB\DataConverter\SerializedToJson;
use Magento\Framework\App\ResourceConnection;
use Magento\Setup\Model\Patch\DataPatchInterface;
use Magento\Setup\Model\Patch\PatchVersionInterface;

/**
 * Class ConvertValidationRulesFromSerializedToJson
 * @package Magento\Customer\Setup\Patch
 */
class ConvertValidationRulesFromSerializedToJson implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var FieldDataConverterFactory
     */
    private $fieldDataConverterFactory;

    /**
     * ConvertValidationRulesFromSerializedToJson constructor.
     * @param ResourceConnection $resourceConnection
     * @param FieldDataConverterFactory $fieldDataConverterFactory
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        FieldDataConverterFactory $fieldDataConverterFactory
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->fieldDataConverterFactory = $fieldDataConverterFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $fieldDataConverter = $this->fieldDataConverterFactory->create(SerializedToJson::class);
        $fieldDataConverter->convert(
            $this->resourceConnection->getConnection(),
            $this->resourceConnection->getConnection()->getTableName('customer_eav_attribute'),
            'attribute_id',
            'validate_rules'
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [
            MigrateStoresAllowedCountriesToWebsite::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getVersion()
    {
        return '2.0.11';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
