<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote\Payment;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Braintree\Model\Ui\PayPal\ConfigProvider;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Model\Quote\Address\Rate;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

/** @var ObjectManager $objectManager */
$objectManager = Bootstrap::getObjectManager();
/** @var QuoteFactory $quoteFactory */
$quoteFactory = Bootstrap::getObjectManager()->get(QuoteFactory::class);
/** @var QuoteResource $quoteResource */
$quoteResource = Bootstrap::getObjectManager()->get(QuoteResource::class);
/** @var StoreManagerInterface $storeManager */
$storeManager = $objectManager->get(StoreManagerInterface::class);
/** @var CartRepositoryInterface $quoteRepository */
$quoteRepository = $objectManager->get(CartRepositoryInterface::class);
$store = $storeManager->getStore();
/** @var Quote $quote */
$quote = $objectManager->create(Quote::class);
$quote->setReservedOrderId('multishipping_quote_id')
    ->setStoreId($store->getId());

$addressList = [
    [
        'firstname' => 'Jonh',
        'lastname' => 'Doe',
        'telephone' => '0333-233-221',
        'street' => ['Main Division 1'],
        'city' => 'Culver City',
        'region' => 'CA',
        'postcode' => 90800,
        'country_id' => 'US',
        'email' => 'customer001@shipping.test',
        'address_type' => 'shipping',
    ],
    [
        'firstname' => 'Antoni',
        'lastname' => 'Holmes',
        'telephone' => '0333-233-221',
        'street' => ['Second Division 2'],
        'city' => 'Denver',
        'region' => 'CO',
        'postcode' => 80203,
        'country_id' => 'US',
        'email' => 'customer002@shipping.test',
        'address_type' => 'shipping'
    ]
];
$methodCode = 'flatrate_flatrate';
foreach ($addressList as $data) {
    /** @var Rate $rate */
    $rate = $objectManager->create(Rate::class);
    $rate->setCode($methodCode)
        ->setPrice(5.00);

    $address = $objectManager->create(AddressInterface::class, ['data' => $data]);
    $address->setShippingMethod($methodCode)
        ->addShippingRate($rate)
        ->setShippingAmount(5.00)
        ->setBaseShippingAmount(5.00);

    $quote->addAddress($address);
}
$quote->setIsMultiShipping(1);
$quoteRepository->save($quote);

Resolver::getInstance()->requireDataFixture('Magento/Multishipping/Fixtures/billing_address.php');
Resolver::getInstance()->requireDataFixture('Magento/Multishipping/Fixtures/items.php');

$quote = $quoteFactory->create();
$quoteResource->load($quote, 'multishipping_quote_id', 'reserved_order_id');
/** @var PaymentInterface $payment */
$payment = $objectManager->create(Payment::class);
$payment->setMethod(ConfigProvider::PAYPAL_CODE);
$quote->setPayment($payment);

$items = $quote->getAllItems();
$addressList = $quote->getAllShippingAddresses();

foreach ($addressList as $key => $address) {
    $item = $items[$key];
    // set correct quantity per shipping address
    $item->setQty(1);
    $address->setTotalQty(1);
    $address->addItem($item);
}

// assign virtual product to the billing address
$billingAddress = $quote->getBillingAddress();
$virtualItem = $items[count($items) - 1];
$billingAddress->setTotalQty(1);
$billingAddress->addItem($virtualItem);

// need to recollect totals
$quote->setTotalsCollectedFlag(false)
    ->setCustomerEmail('customer001@test.com')
    ->collectTotals();
$quoteRepository->save($quote);
