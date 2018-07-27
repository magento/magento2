<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$eavSetupFactory = $objectManager->create(\Magento\Eav\Setup\EavSetupFactory::class);
/** @var \Magento\Eav\Setup\EavSetup $eavSetup */
$eavSetup = $eavSetupFactory->create();
$eavSetup->addAttribute(
    \Magento\Catalog\Model\Product::ENTITY,
    'test_searchable_attribute',
    [
        'label' => 'Test-attribute',
        'is_global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
        'required' => 0,
        'user_defined' => 1,
        'searchable' => 1,
        'visible_on_front' => 1,
        'filterable_in_search' => 1,
        'used_in_product_listing' => 1,
        'is_used_in_grid' => 1,
        'is_filterable_in_grid' => 1,
        'frontend_input' => 'text',
    ]
);

/** @var \Magento\Eav\Model\Config $eavConfig */
$eavConfig = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Eav\Model\Config');
$eavConfig->clear();
