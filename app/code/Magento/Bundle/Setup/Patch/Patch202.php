<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Setup\Patch;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;


/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class Patch202 implements \Magento\Setup\Model\Patch\DataPatchInterface
{


    /**
     * @param EavSetupFactory $eavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * Do Upgrade
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function apply(ModuleDataSetupInterface $setup)
    {
        $setup->startSetup();

        /** @var \Magento\Eav\Setup\EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

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


        $setup->endSetup();

    }

    /**
     * Do Revert
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function revert(ModuleDataSetupInterface $setup)
    {
    }

    /**
     * @inheritdoc
     */
    public function isDisabled()
    {
        return false;
    }


    private function upgradePriceType(EavSetup $eavSetup
    )
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

    private function upgradeSkuType(EavSetup $eavSetup
    )
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

    private function upgradeWeightType(EavSetup $eavSetup
    )
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

    private function upgradeShipmentType(EavSetup $eavSetup
    )
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
}
