<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Sales/_files/quote.php');

$objectManager = Bootstrap::getObjectManager();
/** @var QuoteFactory $quoteFactory */
$quoteFactory = $objectManager->get(QuoteFactory::class);
/** @var QuoteResource $quoteResource */
$quoteResource = $objectManager->get(QuoteResource::class);
$quote = $quoteFactory->create();
$quoteResource->load($quote, 'test01', 'reserved_order_id');

$buyRequest = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    \Magento\Framework\DataObject::class,
    [
        'data' => [
            'qty' => 1,
            'custom_price' => 12,
        ],
    ]
);
/** @var \Magento\Quote\Model\Quote $items */
$items = $quote->getItemsCollection()->getItems();
$quoteItem = reset($items);
$quote->updateItem($quoteItem->getId(), $buyRequest)->save();
