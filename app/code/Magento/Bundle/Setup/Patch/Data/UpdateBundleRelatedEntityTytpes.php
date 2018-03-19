<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Eav\Setup\EavSetup;

/**
 * Class UpdateBundleRelatedEntityTytpes
 * @package Magento\Bundle\Setup\Patch
 */
class UpdateBundleRelatedEntityTytpes implements DataPatchInterface, PatchVersionInterface
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
     * UpdateBundleRelatedEntityTytpes constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        /** @var \Magento\Eav\Setup\EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $attributeSetId = $eavSetup->getDefaultAttributeSetId(ProductAttributeInterface::ENTITY_TYPE_CODE);
        $eavSetup->addAttributeGroup(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            $attributeSetId,
            'Bundle Items',
            16
        );
        $this->upgradePriceType($eavSetup);
        $this->upgradeSkuType($eavSetup);
        $this->upgradeWeightType($eavSetup);
        $this->upgradeShipmentType($eavSetup);
    }

    /**
     * Upgrade Dynamic Price attribute
     *
     * @param EavSetup $eavSetup
     * @return void
     */
    private function upgradePriceType(EavSetup $eavSetup)
    {
        $eavSetup->updateAttribute(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            'price_type',
            'frontend_input',
            'boolean',
            31
        );
        $eavSetup->updateAttribute(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            'price_type',
            'frontend_label',
            'Dynamic Price'
        );
        $eavSetup->updateAttribute(ProductAttributeInterface::ENTITY_TYPE_CODE, 'price_type', 'default_value', 0);
    }

    /**
     * Upgrade Dynamic Sku attribute
     *
     * @param EavSetup $eavSetup
     * @return void
     */
    private function upgradeSkuType(EavSetup $eavSetup)
    {
        $eavSetup->updateAttribute(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            'sku_type',
            'frontend_input',
            'boolean',
            21
        );
        $eavSetup->updateAttribute(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            'sku_type',
            'frontend_label',
            'Dynamic SKU'
        );
        $eavSetup->updateAttribute(ProductAttributeInterface::ENTITY_TYPE_CODE, 'sku_type', 'default_value', 0);
        $eavSetup->updateAttribute(ProductAttributeInterface::ENTITY_TYPE_CODE, 'sku_type', 'is_visible', 1);
    }

    /**
     * Upgrade Dynamic Weight attribute
     *
     * @param EavSetup $eavSetup
     * @return void
     */
    private function upgradeWeightType(EavSetup $eavSetup)
    {
        $eavSetup->updateAttribute(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            'weight_type',
            'frontend_input',
            'boolean',
            71
        );
        $eavSetup->updateAttribute(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            'weight_type',
            'frontend_label',
            'Dynamic Weight'
        );
        $eavSetup->updateAttribute(ProductAttributeInterface::ENTITY_TYPE_CODE, 'weight_type', 'default_value', 0);
        $eavSetup->updateAttribute(ProductAttributeInterface::ENTITY_TYPE_CODE, 'weight_type', 'is_visible', 1);
    }

    /**
     * Upgrade Ship Bundle Items attribute
     *
     * @param EavSetup $eavSetup
     * @return void
     */
    private function upgradeShipmentType(EavSetup $eavSetup)
    {
        $attributeSetId = $eavSetup->getDefaultAttributeSetId(ProductAttributeInterface::ENTITY_TYPE_CODE);
        $eavSetup->addAttributeToGroup(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            $attributeSetId,
            'Bundle Items',
            'shipment_type',
            1
        );
        $eavSetup->updateAttribute(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            'shipment_type',
            'frontend_input',
            'select'
        );
        $eavSetup->updateAttribute(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            'shipment_type',
            'frontend_label',
            'Ship Bundle Items'
        );
        $eavSetup->updateAttribute(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            'shipment_type',
            'source_model',
            \Magento\Bundle\Model\Product\Attribute\Source\Shipment\Type::class
        );
        $eavSetup->updateAttribute(ProductAttributeInterface::ENTITY_TYPE_CODE, 'shipment_type', 'default_value', 0);
        $eavSetup->updateAttribute(ProductAttributeInterface::ENTITY_TYPE_CODE, 'shipment_type', 'is_visible', 1);
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [
            ApplyAttributesUpdate::class,
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
}
