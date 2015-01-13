<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\TestFramework\Helper\Bootstrap;

/** @var \Magento\Framework\Registry $registry */
$registry = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Framework\Registry');
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/** @var \Magento\Catalog\Model\Category $category */
$category = Bootstrap::getObjectManager()->get('Magento\Catalog\Model\Category');
$category = $category->loadByAttribute('url_key', 'test-category-name');

if ($category && $category->getId()) {
    $category->delete();
}
