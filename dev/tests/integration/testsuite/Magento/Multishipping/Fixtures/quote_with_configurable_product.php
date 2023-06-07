<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Session;
use Magento\Eav\Model\Config;
use Magento\Framework\DataObject;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Rate;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/ConfigurableProduct/_files/configurable_products.php');

/** @var ObjectManager $objectManager */
$objectManager = Bootstrap::getObjectManager();
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->create(ProductRepositoryInterface::class);
$product = $productRepository->get('simple_10');
$product->setStockData(['use_config_manage_stock' => 1, 'qty' => 4, 'is_qty_decimal' => 0, 'is_in_stock' => 1]);
$productRepository->save($product);

/** @var Quote $quote */
$quote = $objectManager->create(Quote::class);
$request = $objectManager->create(DataObject::class);

/** @var Config $eavConfig */
$eavConfig = $objectManager->get(Config::class);
/** @var  $attribute */
$attribute = $eavConfig->getAttribute('catalog_product', 'test_configurable');

$request->setData(
    [
        'product_id' => $productRepository->get('configurable')->getId(),
        'selected_configurable_option' => '1',
        'super_attribute' => [
            $attribute->getAttributeId() => $attribute->getOptions()[1]->getValue()
        ],
        'qty' => '2'
    ]
);

$quote->setStoreId(1)
    ->setIsActive(
        true
    )->setIsMultiShipping(
        1
    )->setReservedOrderId(
        'test_order_with_configurable_product'
    )->setCustomerEmail(
        'store@example.com'
    )->addProduct(
        $productRepository->get('configurable'),
        $request
    );

/** @var PaymentInterface $payment */
$payment = $objectManager->create(PaymentInterface::class);
$payment->setMethod('checkmo');
$quote->setPayment($payment);

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
        'email' => 'store@example.com',
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
    ],
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

/** @var CartRepositoryInterface $quoteRepository */
$quoteRepository = $objectManager->get(CartRepositoryInterface::class);
$quoteRepository->save($quote);

$items = $quote->getAllItems();

foreach ($quote->getAllShippingAddresses() as $address) {
    foreach ($items as $item) {
        $item->setQty(1);
        $address->setTotalQty(1);
        $address->addItem($item);
    }
}

$billingAddressData = [
    'firstname' => 'Jonh',
    'lastname' => 'Doe',
    'telephone' => '0333-233-221',
    'street' => ['Third Division 1'],
    'city' => 'New York',
    'region' => 'NY',
    'postcode' => 10029,
    'country_id' => 'US',
    'email' => 'store@example.com',
    'address_type' => 'billing',
];

/** @var AddressInterface $address */
$billingAddress = $objectManager->create(AddressInterface::class, ['data' => $billingAddressData]);
$quote->setBillingAddress($billingAddress);
$quote->collectTotals();
$quoteRepository->save($quote);

/** @var Session $session */
$session = $objectManager->get(Session::class);
$session->setQuoteId($quote->getId());
