<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */

/** @var $objectManager \Magento\TestFramework\ObjectManager */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$objectManager->get(\Magento\Framework\Registry::class)->unregister('_singleton/Magento\Checkout\Model\Session');
$objectManager->get(\Magento\Framework\Registry::class)->unregister('_singleton/Magento_Checkout_Model_Cart');
/** @var $cart \Magento\Checkout\Model\Cart */
$cart = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(\Magento\Checkout\Model\Cart::class);

$cart->addProduct($product, $requestInfo);
$cart->save();

$quoteItemId = $cart->getQuote()->getItemByProduct($product)->getId();
$objectManager->get(\Magento\Framework\Registry::class)->register('product/quoteItemId', $quoteItemId);
$objectManager->get(\Magento\Framework\Registry::class)->unregister('_singleton/Magento\Checkout\Model\Session');
$objectManager->get(\Magento\Framework\Registry::class)->unregister('_singleton/Magento_Checkout_Model_Cart');
