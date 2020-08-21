<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Quote\Api\Data\AddressInterface;

$objectManager = Bootstrap::getObjectManager();
/** @var QuoteFactory $quoteFactory */
$quoteFactory = Bootstrap::getObjectManager()->get(QuoteFactory::class);
/** @var QuoteResource $quoteResource */
$quoteResource = Bootstrap::getObjectManager()->get(QuoteResource::class);
$quote = $quoteFactory->create();
$quoteResource->load($quote, 'multishipping_quote_id', 'reserved_order_id');

$data = [
    'firstname' => 'Jonh',
    'lastname' => 'Doe',
    'telephone' => '0333-233-221',
    'street' => ['Third Division 1'],
    'city' => 'New York',
    'region' => 'NY',
    'postcode' => 10029,
    'country_id' => 'US',
    'email' => 'customer001@billing.test',
    'address_type' => 'billing',
];

/** @var AddressInterface $address */
$address = $objectManager->create(AddressInterface::class, ['data' => $data]);
$quote->setBillingAddress($address);
$quoteResource->save($quote);
