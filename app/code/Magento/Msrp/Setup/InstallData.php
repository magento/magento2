<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Msrp\Setup;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * @codeCoverageIgnore
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InstallData implements InstallDataInterface
{
    /**
     * EAV setup factory
     *
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * Init
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
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        $productTypes = [
            \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE,
            \Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL,
            \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE,
            \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE,
        ];
        $productTypes = join(',', $productTypes);

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'msrp',
            [
                'group' => 'Advanced Pricing',
                'backend' => \Magento\Catalog\Model\Product\Attribute\Backend\Price::class,
                'frontend' => '',
                'label' => 'Manufacturer\'s Suggested Retail Price',
                'type' => 'decimal',
                'input' => 'price',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_WEBSITE,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'apply_to' => $productTypes,
                'input_renderer' => \Magento\Msrp\Block\Adminhtml\Product\Helper\Form\Type::class,
                'frontend_input_renderer' => \Magento\Msrp\Block\Adminhtml\Product\Helper\Form\Type::class,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'is_used_in_grid' => true,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => true,
            ]
        );

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'msrp_display_actual_price_type',
            [
                'group' => 'Advanced Pricing',
                'backend' => \Magento\Catalog\Model\Product\Attribute\Backend\Boolean::class,
                'frontend' => '',
                'label' => 'Display Actual Price',
                'input' => 'select',
                'source' => \Magento\Msrp\Model\Product\Attribute\Source\Type\Price::class,
                'source_model' => \Magento\Msrp\Model\Product\Attribute\Source\Type\Price::class,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_WEBSITE,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => \Magento\Msrp\Model\Product\Attribute\Source\Type\Price::TYPE_USE_CONFIG,
                'default_value' => \Magento\Msrp\Model\Product\Attribute\Source\Type\Price::TYPE_USE_CONFIG,
                'apply_to' => $productTypes,
                'input_renderer' => \Magento\Msrp\Block\Adminhtml\Product\Helper\Form\Type\Price::class,
                'frontend_input_renderer' => \Magento\Msrp\Block\Adminhtml\Product\Helper\Form\Type\Price::class,
                'visible_on_front' => false,
                'used_in_product_listing' => true
            ]
        );
    }
}
