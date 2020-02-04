<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

$setup = $objectManager->get(ModuleDataSetupInterface::class);
/** @var EavSetup $eavSetup */
$eavSetup = $objectManager->get(EavSetupFactory::class)
                          ->create(['setup' => $setup]);
$eavSetup->addAttribute(
    \Magento\Catalog\Model\Product::ENTITY,
    'zzz',
    [
        'type' => 'int',
        'backend' => '',
        'frontend' => '',
        'label' => 'zzz',
        'input' => 'select',
        'class' => '',
        'source' => '',
        'global' => 1,
        'visible' => true,
        'required' => true,
        'user_defined' => true,
        'default' => null,
        'searchable' => false,
        'filterable' => false,
        'comparable' => false,
        'visible_on_front' => false,
        'used_in_product_listing' => false,
        'unique' => true,
        'apply_to' => '',
        'system' => 1,
        'group' => 'General',
        'option' => ['values' => ["Black", "White", "Red", "Brown", "zzz", "Metallic"]]
    ]
);
