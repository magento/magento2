<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Setup\Patch\Data;

use Magento\Framework\DB\FieldDataConverterFactory;
use Magento\Framework\DB\DataConverter\SerializedToJson;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

/**
 * Class convert validation rules from serialized to json for customer
 */
class ConvertValidationRulesFromSerializedToJson implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var FieldDataConverterFactory
     */
    private $fieldDataConverterFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param FieldDataConverterFactory $fieldDataConverterFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        FieldDataConverterFactory $fieldDataConverterFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->fieldDataConverterFactory = $fieldDataConverterFactory;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $fieldDataConverter = $this->fieldDataConverterFactory->create(SerializedToJson::class);
        $fieldDataConverter->convert(
            $this->moduleDataSetup->getConnection(),
            $this->moduleDataSetup->getTable('customer_eav_attribute'),
            'attribute_id',
            'validate_rules'
        );

        return $this;
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [
            MigrateStoresAllowedCountriesToWebsite::class,
        ];
    }

    /**
     * @inheritdoc
     */
    public static function getVersion()
    {
        return '2.0.11';
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
