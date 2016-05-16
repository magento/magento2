<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\TestFramework\Helper\Bootstrap;

/** @var \Magento\Framework\Registry $registry */
$registry = Bootstrap::getObjectManager()->get('Magento\Framework\Registry');
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/** @var $order \Magento\Sales\Model\Order */
$orderCollection = Bootstrap::getObjectManager()->create('Magento\Sales\Model\ResourceModel\Order\Collection');
foreach ($orderCollection as $order) {
    $order->delete();
}

/** @var $product \Magento\Catalog\Model\Product */
$productCollection = Bootstrap::getObjectManager()->create('Magento\Catalog\Model\ResourceModel\Product\Collection');
foreach ($productCollection as $product) {
    $product->delete();
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
