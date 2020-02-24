<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Framework\App\Config;
use Magento\Store\Model\StoreRepository;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteRepository;
use Magento\Store\Model\Store;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;

require __DIR__ . '/../../Store/_files/second_store.php';

/** @var $objectManager ObjectManager */
$objectManager = Bootstrap::getObjectManager();
/** @var  QuoteFactory $quoteFactory */
$quoteFactory = $objectManager->get(QuoteFactory::class);
/** @var QuoteRepository $quoteRepository */
$quoteRepository = $objectManager->get(QuoteRepository::class);
/** @var  StoreRepository $storeRepository */
$storeRepository = $objectManager->get(StoreRepository::class);
/** @var Config $appConfig */
$appConfig = $objectManager->get(Config::class);
$appConfig->clean();

/** @var Store $defaultStore */
$defaultStore = $storeRepository->getActiveStoreByCode('default');
/** @var Store $secondStore */
$secondStore = $storeRepository->getActiveStoreByCode('fixture_second_store');

$quotes = [
    'quote for first store' => [
        'store' => $defaultStore->getId(),
    ],
    'quote for second store' => [
        'store' => $secondStore->getId(),
    ],
];

foreach ($quotes as $quoteData) {
    /** @var Quote $quote */
    $quote = $quoteFactory->create();
    $quote->setStoreId($quoteData['store']);
    $quoteRepository->save($quote);
}
