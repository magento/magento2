<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

require __DIR__ . '/../../../Magento/Customer/_files/customer.php';
require __DIR__ . '/../../../Magento/Catalog/_files/product_simple.php';

/** @var $simpleProduct \Magento\Catalog\Model\Product */
$simpleProduct = $product->load($product->getId());

$options = [];
foreach ($simpleProduct->getOptions() as $option) {
    /* @var $option \Magento\Catalog\Model\Product\Option */
    switch ($option->getType()) {
        case 'field':
            $options[$option->getId()] = '1-text';
            break;
        case 'date_time':
            $options[$option->getId()] = ['month' => 1, 'day' => 1, 'year' => 2001, 'hour' => 1, 'minute' => 1];
            break;
        case 'drop_down':
            $options[$option->getId()] = '1';
            break;
        case 'radio':
            $options[$option->getId()] = '1';
            break;
    }
}

/* @var $wishlist \Magento\Wishlist\Model\Wishlist */
$wishlist = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Wishlist\Model\Wishlist');
$wishlist->loadByCustomerId($customer->getId(), true);
$wishlist->addNewItem($simpleProduct, new \Magento\Framework\DataObject(['options' => $options]));
$wishlist->setSharingCode('fixture_unique_code')
    ->setShared(1)
    ->save();
