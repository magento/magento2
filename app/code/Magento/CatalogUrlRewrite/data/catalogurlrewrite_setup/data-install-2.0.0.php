<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var $this \Magento\Catalog\Model\Resource\Setup */
$this->addAttribute(
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

$this->addAttribute(
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

$this->addAttribute(
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

$this->addAttribute(
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
