<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/** @var $this \Magento\Catalog\Model\Resource\Setup */
$this->addAttribute(
    \Magento\Catalog\Model\Category::ENTITY,
    'url_key',
    array(
        'type' => 'varchar',
        'label' => 'URL Key',
        'input' => 'text',
        'required' => false,
        'sort_order' => 3,
        'global' => \Magento\Catalog\Model\Resource\Eav\Attribute::SCOPE_STORE,
        'group' => 'General Information'
    )
);

$this->addAttribute(
    \Magento\Catalog\Model\Category::ENTITY,
    'url_path',
    array(
        'type' => 'varchar',
        'required' => false,
        'sort_order' => 17,
        'global' => \Magento\Catalog\Model\Resource\Eav\Attribute::SCOPE_STORE,
        'visible' => false,
    )
);

$this->addAttribute(
    \Magento\Catalog\Model\Product::ENTITY,
    'url_key',
    array(
        'type' => 'varchar',
        'label' => 'URL Key',
        'input' => 'text',
        'required' => false,
        'sort_order' => 10,
        'global' => \Magento\Catalog\Model\Resource\Eav\Attribute::SCOPE_STORE,
        'used_in_product_listing' => true,
        'group' => 'Search Engine Optimization'
    )
);

$this->addAttribute(
    \Magento\Catalog\Model\Product::ENTITY,
    'url_path',
    array(
        'type' => 'varchar',
        'required' => false,
        'sort_order' => 11,
        'global' => \Magento\Catalog\Model\Resource\Eav\Attribute::SCOPE_STORE,
        'visible' => false,
    )
);
