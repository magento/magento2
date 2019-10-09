<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\QuoteRepository;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;

require dirname(dirname(__DIR__)) . '/Store/_files/second_store.php';

/** @var $objectManager ObjectManager */
$objectManager = Bootstrap::getObjectManager();
/** @var  QuoteFactory $quoteFactory */
$quoteFactory = $objectManager->get(QuoteFactory::class);
/** @var QuoteRepository $quoteRepository */
$quoteRepository = $objectManager->get(QuoteRepository::class);

$quotes = [
    'quote for first store' => [
        'store' => 1,
    ],
    'quote for second store' => [
        'store' => 2,
    ],
];

foreach ($quotes as $quoteData) {
    $quote = $quoteFactory->create();
    $quote->setStoreId($quoteData['store']);
    $quoteRepository->save($quote);
}
