<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\QuoteRepository;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;

require dirname(dirname(__DIR__)) . '/Store/_files/second_store.php';

/** @var $objectManager ObjectManager */
$objectManager = Bootstrap::getObjectManager();
/** @var  QuoteFactory $quoteFactory */
$quoteFactory = $objectManager->get(QuoteFactory::class);
/** @var QuoteRepository $quoteRepository */
$quoteRepository = $objectManager->get(QuoteRepository::class);
/** @var StoreManager $storeManager */
$storeManager = $objectManager->get(StoreManager::class);

$quotes = [
    'quote for first store' => [
        'store' => 'default',
    ],
    'quote for second store' => [
        'store' => 'fixture_second_store',
    ],
];

foreach ($quotes as $quoteData) {
    $quote = $quoteFactory->create();
    /** @var StoreInterface $store */
    $store = $storeManager->getStore($quoteData['store']);
    $quote->setStoreId($store->getId());
    $quoteRepository->save($quote);
}
