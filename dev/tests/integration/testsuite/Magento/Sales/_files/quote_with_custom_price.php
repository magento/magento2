<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

include __DIR__ . '/quote.php';

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
