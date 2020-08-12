<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/ConfigurableProduct/_files/product_configurable.php');
/** @var $attribute \Magento\Catalog\Model\ResourceModel\Eav\Attribute */

$objectManager = Bootstrap::getObjectManager();
/** @var $product \Magento\Catalog\Model\Product */
$productRepository = $objectManager->create(ProductRepositoryInterface::class);
$product = $productRepository->get('configurable');
/* Create simple products per each option */
/** @var $options \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection */
$options = $objectManager->create(
    \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection::class
);
/** @var \Magento\Eav\Model\Config $eavConfig */
$eavConfig = $objectManager->get(\Magento\Eav\Model\Config::class);
$attribute = $eavConfig->getAttribute(\Magento\Catalog\Model\Product::ENTITY, 'test_configurable');
$option = $options->setAttributeFilter($attribute->getId())->getFirstItem();

$requestInfo = new \Magento\Framework\DataObject(
    [
        'product' => 1,
        'selected_configurable_option' => 1,
        'qty' => 1,
        'super_attribute' => [
            $attribute->getId() => $option->getId()
        ]
    ]
);

/** @var $cart \Magento\Checkout\Model\Cart */
$cart = $objectManager->create(\Magento\Checkout\Model\Cart::class);
$cart->addProduct($product, $requestInfo);
$cart->getQuote()->setReservedOrderId('test_cart_with_configurable');
$cart->save();

/** @var $objectManager \Magento\TestFramework\ObjectManager */
$objectManager->removeSharedInstance(\Magento\Checkout\Model\Session::class);

/** @var \Magento\Quote\Model\QuoteIdMask $quoteIdMask */
$quoteIdMask = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->create(\Magento\Quote\Model\QuoteIdMaskFactory::class)
    ->create();
$quoteIdMask->setQuoteId($cart->getQuote()->getId());
$quoteIdMask->setDataChanges(true);
$quoteIdMask->save();
