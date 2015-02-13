<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Setup;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataResourceInterface;

class InstallData implements InstallDataInterface
{
    /**
     * @var EavSetupFactory
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
     * {@inheritdoc}
     */
    public function install(ModuleDataResourceInterface $setup, ModuleContextInterface $context)
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Category::ENTITY,
            'url_key',
            [
                'type' => 'varchar',
                'label' => 'URL Key',
                'input' => 'text',
                'required' => false,
                'sort_order' => 3,
                'global' => \Magento\Catalog\Model\Resource\Eav\Attribute::SCOPE_STORE,
                'group' => 'General Information',
            ]
        );

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Category::ENTITY,
            'url_path',
            [
                'type' => 'varchar',
                'required' => false,
                'sort_order' => 17,
                'global' => \Magento\Catalog\Model\Resource\Eav\Attribute::SCOPE_STORE,
                'visible' => false,
                'group' => 'General Information',
            ]
        );

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'url_key',
            [
                'type' => 'varchar',
                'label' => 'URL Key',
                'input' => 'text',
                'required' => false,
                'sort_order' => 10,
                'global' => \Magento\Catalog\Model\Resource\Eav\Attribute::SCOPE_STORE,
                'used_in_product_listing' => true,
                'group' => 'Search Engine Optimization',
            ]
        );

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'url_path',
            [
                'type' => 'varchar',
                'required' => false,
                'sort_order' => 11,
                'global' => \Magento\Catalog\Model\Resource\Eav\Attribute::SCOPE_STORE,
                'visible' => false,
            ]
        );
    }
}
