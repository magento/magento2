<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Model\Quote\Address\Rate;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteRepository;

/** @var $objectManager \Magento\TestFramework\ObjectManager */
$objectManager = Bootstrap::getObjectManager();
/** @var Quote $quote */
$quote = $objectManager->create(Quote::class);

/** @var AddressInterface $billingAddress */
$billingAddress = $objectManager->create(AddressInterface::class);
$billingAddress->setFirstname('Joe')
    ->setLastname('Doe')
    ->setCountryId('US')
    ->setRegion('TX')
    ->setCity('Austin')
    ->setStreet('1000 West Parmer Line')
    ->setPostcode('11501')
    ->setTelephone('123456789');
$quote->setBillingAddress($billingAddress);

/** @var AddressInterface $shippingAddress */
$shippingAddress = $objectManager->create(AddressInterface::class);
$shippingAddress->setFirstname('Joe')
    ->setLastname('Doe')
    ->setCountryId('US')
    ->setRegion('TX')
    ->setCity('Austin')
    ->setStreet('1000 West Parmer Line')
    ->setPostcode('11501')
    ->setTelephone('123456789');
$quote->setShippingAddress($shippingAddress);

$quote->getShippingAddress()
    ->setShippingMethod('flatrate_flatrate')
    ->setCollectShippingRates(1);
/** @var Rate $shippingRate */
$shippingRate = $objectManager->create(Rate::class);
$shippingRate->setMethod('flatrate')
    ->setCarrier('flatrate')
    ->setPrice(5)
    ->setCarrierTitle('Flat Rate')
    ->setCode('flatrate_flatrate');
$quote->getShippingAddress()
    ->addShippingRate($shippingRate);

$quote->getPayment()->setMethod('CC');
$quote->setReservedOrderId('quote123');
$quote->setStoreId(1);

/** @var QuoteRepository $quoteRepository */
$quoteRepository = $objectManager->create(\Magento\Quote\Api\CartRepositoryInterface::class);
$quoteRepository->save($quote);
