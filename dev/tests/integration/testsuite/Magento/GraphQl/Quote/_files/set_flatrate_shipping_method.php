<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Checkout\Api\Data\ShippingInformationInterfaceFactory;
use Magento\Checkout\Api\ShippingInformationManagementInterface;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Magento\TestFramework\Helper\Bootstrap;

/** @var QuoteFactory $quoteFactory */
$quoteFactory = Bootstrap::getObjectManager()->get(QuoteFactory::class);
/** @var QuoteResource $quoteResource */
$quoteResource = Bootstrap::getObjectManager()->get(QuoteResource::class);
/** @var ShippingInformationInterfaceFactory $shippingInformationFactory */
$shippingInformationFactory = Bootstrap::getObjectManager()->get(ShippingInformationInterfaceFactory::class);
/** @var ShippingInformationManagementInterface $shippingInformationManagement */
$shippingInformationManagement = Bootstrap::getObjectManager()->get(ShippingInformationManagementInterface::class);

$quote = $quoteFactory->create();
$quoteResource->load($quote, 'test_quote', 'reserved_order_id');
$quoteAddress = $quote->getShippingAddress();

/** @var ShippingInformationInterface $shippingInformation */
$shippingInformation = $shippingInformationFactory->create([
    'data' => [
        ShippingInformationInterface::SHIPPING_ADDRESS => $quoteAddress,
        ShippingInformationInterface::SHIPPING_CARRIER_CODE => 'flatrate',
        ShippingInformationInterface::SHIPPING_METHOD_CODE => 'flatrate',
    ],
]);
$shippingInformationManagement->saveAddressInformation($quote->getId(), $shippingInformation);
