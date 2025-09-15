<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

namespace Magento\CatalogInventory\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

/**
 * Class CreateDefaultStock patch
 */
class CreateDefaultStock implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * PrepareInitialConfig constructor.
     * @param ModuleDataSetupInterface $resourceConnection
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $resourceConnection,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->moduleDataSetup = $resourceConnection;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * Run code inside patch
     * If code fails, patch must be reverted, in case when we are speaking about schema - then under revert
     * means run PatchInterface::revert()
     *
     * If we speak about data, under revert means: $transaction->rollback()
     *
     * @return void
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()
            ->insertForce(
                $this->moduleDataSetup->getTable('cataloginventory_stock'),
                ['stock_id' => 1, 'stock_name' => 'Default']
            );

        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $groupName = 'Product Details';
        $entityTypeId = $eavSetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);
        $attributeSetId = $eavSetup->getAttributeSetId($entityTypeId, 'Default');
        $attribute = $eavSetup->getAttribute($entityTypeId, 'quantity_and_stock_status');
        if ($attribute) {
            $eavSetup->addAttributeToGroup($entityTypeId, $attributeSetId, $groupName, $attribute['attribute_id'], 60);
            $eavSetup->updateAttribute($entityTypeId, $attribute['attribute_id'], 'default_value', 1);
        }
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function getVersion()
    {
        return '2.0.0';
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
