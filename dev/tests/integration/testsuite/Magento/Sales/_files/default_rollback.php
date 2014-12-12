<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

use Magento\TestFramework\Helper\Bootstrap;

/** @var \Magento\Framework\Registry $registry */
$registry = Bootstrap::getObjectManager()->get('Magento\Framework\Registry');
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/** @var $order \Magento\Sales\Model\Order */
$orderCollection = Bootstrap::getObjectManager()->create('Magento\Sales\Model\Resource\Order\Collection');
foreach ($orderCollection as $order) {
    $order->delete();
}

/** @var $product \Magento\Catalog\Model\Product */
$productCollection = Bootstrap::getObjectManager()->create('Magento\Catalog\Model\Resource\Product\Collection');
foreach ($productCollection as $product) {
    $product->delete();
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
