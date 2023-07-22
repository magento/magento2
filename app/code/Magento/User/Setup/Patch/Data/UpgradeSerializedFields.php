<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Setup\Patch\Data;

use Magento\Framework\DB\DataConverter\SerializedToJson;
use Magento\Framework\DB\FieldDataConverterFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

/**
 * Class UpgradeSerializedFields
 * @package Magento\User\Setup\Patch
 */
class UpgradeSerializedFields implements DataPatchInterface, PatchVersionInterface
{
    /**
     * UpgradeSerializedFields constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param FieldDataConverterFactory $fieldDataConverterFactory
     */
    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup,
        private readonly FieldDataConverterFactory $fieldDataConverterFactory
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $this->upgradeSerializedFields();
        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [
            UpgradePasswordHashes::class
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '2.0.2';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * Convert serialized data to json.
     */
    private function upgradeSerializedFields()
    {
        $fieldDataConverter = $this->fieldDataConverterFactory->create(SerializedToJson::class);
        $fieldDataConverter->convert(
            $this->moduleDataSetup->getConnection(),
            $this->moduleDataSetup->getTable('admin_user'),
            'user_id',
            'extra'
        );
    }
}
