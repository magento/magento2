<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;

Resolver::getInstance()->requireDataFixture('Magento/Paypal/_files/quote_express_with_customer.php');

/** @var QuoteFactory $quoteFactory */
$quoteFactory = Bootstrap::getObjectManager()->get(QuoteFactory::class);
/** @var QuoteResource $quoteResource */
$quoteResource = Bootstrap::getObjectManager()->get(QuoteResource::class);
$quote = $quoteFactory->create();
$quoteResource->load($quote, 'test02', 'reserved_order_id');

$objectManager = Bootstrap::getObjectManager();
/** @var $service \Magento\Quote\Api\CartManagementInterface */
$service = $objectManager->create(\Magento\Quote\Api\CartManagementInterface::class);
$order = $service->submit($quote, ['increment_id' => '100000002']);
