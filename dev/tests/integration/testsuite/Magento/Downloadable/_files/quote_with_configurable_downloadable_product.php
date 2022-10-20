<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection;
use Magento\Framework\DataObject;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Downloadable/_files/product_configurable_downloadable.php');

$objectManager = Bootstrap::getObjectManager();
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->create(ProductRepositoryInterface::class);
$downloadableProduct = $productRepository->get('downloadable-product');
$configurableProduct = $productRepository->get('configurable_downloadable');
/** @var $options Collection */
$options = $objectManager->create(Collection::class);
/** @var Config $eavConfig */
$eavConfig = $objectManager->get(Config::class);
$attribute = $eavConfig->getAttribute(Product::ENTITY, 'test_configurable');
$option = $options->setAttributeFilter($attribute->getId())->getFirstItem();
$requestInfo = new DataObject(
    [
        'product_id' => $configurableProduct->getId(),
        'selected_configurable_option' => $downloadableProduct->getId(),
        'qty' => 1,
        'super_attribute' => [
            $attribute->getId() => $option->getId()
        ],
        'links' => array_keys($downloadableProduct->getDownloadableLinks())
    ]
);
$addressData = [
    'telephone' => 3234676,
    'postcode' => 47676,
    'country_id' => 'DE',
    'city' => 'CityX',
    'street' => ['Black str, 48'],
    'lastname' => 'Smith',
    'firstname' => 'John',
    'vat_id' => 12345,
    'address_type' => 'shipping',
    'email' => 'some_email@mail.com',
];

$billingAddress = $objectManager->create(
    Address::class,
    ['data' => $addressData]
);
$billingAddress->setAddressType('billing');
$shippingAddress = clone $billingAddress;
$shippingAddress->setId(null)->setAddressType('shipping');

/** @var Quote $quote */
$quote = $objectManager->create(Quote::class);
$quote->setCustomerIsGuest(true)
    ->setStoreId($objectManager->get(StoreManagerInterface::class)->getStore()->getId())
    ->setReservedOrderId('reserved_order_configurable_downloadable')
    ->setIsMultiShipping(false)
    ->setBillingAddress($billingAddress)
    ->setShippingAddress($shippingAddress)
    ->addProduct($configurableProduct, $requestInfo);

$quote->getPayment()->setMethod('checkmo');
$quote->getShippingAddress()->setShippingMethod('flatrate_flatrate')->setCollectShippingRates(true);
$quote->collectTotals();
$quote->save();

/** @var QuoteIdMask $quoteIdMask */
$quoteIdMask = $objectManager
    ->create(QuoteIdMaskFactory::class)
    ->create();
$quoteIdMask->setQuoteId($quote->getId());
$quoteIdMask->setDataChanges(true);
$quoteIdMask->save();
