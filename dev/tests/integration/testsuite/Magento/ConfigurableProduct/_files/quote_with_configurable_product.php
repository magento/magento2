<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

require 'product_configurable.php';
/** @var $attribute \Magento\Catalog\Model\ResourceModel\Eav\Attribute */

/** @var $product \Magento\Catalog\Model\Product */
$product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Catalog\Model\Product');
$product->load(1);
/* Create simple products per each option */
/** @var $options \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection */
$options = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    'Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection'
);
$option = $options->setAttributeFilter($attribute->getId())->getFirstItem();

$requestInfo = new \Magento\Framework\DataObject(
    ['qty' => 1, 'super_attribute' => [$attribute->getId() => $option->getId()]]
);

/** @var $cart \Magento\Checkout\Model\Cart */
$cart = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Checkout\Model\Cart');
$cart->addProduct($product, $requestInfo);
$cart->getQuote()->setReservedOrderId('test_cart_with_configurable');
$cart->save();

/** @var $objectManager \Magento\TestFramework\ObjectManager */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$objectManager->removeSharedInstance('Magento\Checkout\Model\Session');
