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
/** @var \Magento\Store\Model\Store $secondStore */
$secondStore = $objectManager->get(\Magento\Store\Api\StoreRepositoryInterface::class)->get('fixture_second_store');
$secondStoreId = $secondStore->getId();
$quotes = [
    'quote for first store' => [
        'store' => 1,
    ],
    'quote for second store' => [
   //     'store' => 2,
        'store' => $secondStoreId,
    ],
];

foreach ($quotes as $quoteData) {
    $quote = $quoteFactory->create();
    $quote->setStoreId($quoteData['store']);
    $quoteRepository->save($quote);
}
