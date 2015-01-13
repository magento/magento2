<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var $objectManager \Magento\TestFramework\ObjectManager */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$objectManager->get('Magento\Framework\Registry')->unregister('_singleton/Magento\Checkout\Model\Session');
$objectManager->get('Magento\Framework\Registry')->unregister('_singleton/Magento_Checkout_Model_Cart');
/** @var $cart \Magento\Checkout\Model\Cart */
$cart = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Checkout\Model\Cart');

$cart->addProduct($product, $requestInfo);
$cart->save();

$quoteItemId = $cart->getQuote()->getItemByProduct($product)->getId();
$objectManager->get('Magento\Framework\Registry')->register('product/quoteItemId', $quoteItemId);
$objectManager->get('Magento\Framework\Registry')->unregister('_singleton/Magento\Checkout\Model\Session');
$objectManager->get('Magento\Framework\Registry')->unregister('_singleton/Magento_Checkout_Model_Cart');
