<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Setup;

use Magento\Catalog\Model\Product;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Eav\Setup\EavSetupFactory;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var EavSetupFactory
     */
    protected $eavSetupFactory;

    /**
     * UpgradeData constructor
     *
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '2.0.2', '<')) {
            /** @var \Magento\Eav\Setup\EavSetup $eavSetup */
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

            $eavSetup->updateAttribute(Product::ENTITY, 'price_type', 'frontend_input', 'boolean');
            $eavSetup->updateAttribute(
                Product::ENTITY,
                'price_type',
                'source_model',
                'Magento\Bundle\Model\Product\Attribute\Source\Price\Type'
            );
            $eavSetup->updateAttribute(Product::ENTITY, 'sku_type', 'frontend_input', 'boolean');
            $eavSetup->updateAttribute(
                Product::ENTITY,
                'sku_type',
                'source_model',
                'Magento\Bundle\Model\Product\Attribute\Source\Price\Type'
            );
            $eavSetup->updateAttribute(Product::ENTITY, 'weight_type', 'frontend_input', 'boolean');
            $eavSetup->updateAttribute(
                Product::ENTITY,
                'weight_type',
                'source_model',
                'Magento\Bundle\Model\Product\Attribute\Source\Price\Type'
            );
            $eavSetup->updateAttribute(Product::ENTITY, 'shipment_type', 'frontend_input', 'select');
            $eavSetup->updateAttribute(Product::ENTITY, 'shipment_type', 'frontend_label', __('Ship Bundle Items'), 1);
            $eavSetup->updateAttribute(
                Product::ENTITY,
                'shipment_type',
                'source_model',
                'Magento\Bundle\Model\Product\Attribute\Source\Shipment\Type'
            );
        }

        $setup->endSetup();
    }
}
