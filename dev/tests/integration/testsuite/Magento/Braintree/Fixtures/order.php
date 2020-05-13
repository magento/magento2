<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Address;
use Magento\Sales\Model\Order\Item as OrderItem;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;
use Magento\Braintree\Model\Ui\ConfigProvider;
use Magento\Sales\Api\Data\OrderPaymentExtensionInterfaceFactory;
use Magento\Sales\Model\Order\Payment;
use Magento\Vault\Model\PaymentToken;

Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/product_simple.php');
/** @var ObjectManager $objectManager */
$objectManager = Bootstrap::getObjectManager();
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->create(ProductRepositoryInterface::class);
$product = $productRepository->get('simple');
$addressData = include __DIR__ . '/../../Sales/_files/address_data.php';

$billingAddress = $objectManager->create(Address::class, ['data' => $addressData]);
$billingAddress->setAddressType('billing');

$shippingAddress = clone $billingAddress;
$shippingAddress->setId(null)
    ->setAddressType('shipping');

/** @var OrderItem $orderItem */
$orderItem = $objectManager->create(OrderItem::class);
$orderItem->setProductId($product->getId())
    ->setQtyOrdered(2)
    ->setBasePrice($product->getPrice())
    ->setPrice($product->getPrice())
    ->setRowTotal($product->getPrice())
    ->setProductType('simple');

Resolver::getInstance()->requireDataFixture('Magento/Vault/_files/token.php');
/** @var ObjectManager $objectManager */
$objectManager = Bootstrap::getObjectManager();
/** @var PaymentToken $token */
$token = $objectManager->create(PaymentToken::class);
$token->load('vault_payment', 'payment_method_code');
$token->setPaymentMethodCode(ConfigProvider::CODE);
/** @var OrderPaymentExtensionInterfaceFactory $paymentExtensionFactory */
$paymentExtensionFactory = $objectManager->get(OrderPaymentExtensionInterfaceFactory::class);
$extensionAttributes = $paymentExtensionFactory->create();
$extensionAttributes->setVaultPaymentToken($token);

/** @var Payment $payment */
$payment = $objectManager->create(Payment::class);
$payment->setMethod(ConfigProvider::CODE);
$payment->setExtensionAttributes($extensionAttributes);
$payment->setAuthorizationTransaction(true);

$order = $objectManager->create(Order::class);
$order->setIncrementId('100000002')
    ->setSubtotal($product->getPrice() * 2)
    ->setBaseSubtotal($product->getPrice() * 2)
    ->setCustomerEmail('admin@example.com')
    ->setCustomerIsGuest(true)
    ->setBillingAddress($billingAddress)
    ->setShippingAddress($shippingAddress)
    ->setStoreId(
        $objectManager->get(StoreManagerInterface::class)->getStore()
            ->getId()
    )
    ->addItem($orderItem)
    ->setPayment($payment);

/** @var OrderRepositoryInterface $orderRepository */
$orderRepository = $objectManager->get(OrderRepositoryInterface::class);
$orderRepository->save($order);
