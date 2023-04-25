<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Model\ProductRepository;
use Magento\Quote\Model\Quote\Item\Updater;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\ResourceModel\Quote;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Sales/_files/quote_with_two_products_and_customer.php');

$objectManager = Bootstrap::getObjectManager();
/** @var QuoteFactory $quoteFactory */
$quoteFactory = $objectManager->get(QuoteFactory::class);
/** @var Quote $quoteResource */
$quoteResource = $objectManager->get(Quote::class);
$quote = $quoteFactory->create();
$quoteResource->load($quote, 'test01', 'reserved_order_id');
$productRepository = $objectManager->get(ProductRepository::class);
$quoteItem = $quote->getItemByProduct($productRepository->get('simple'));
/** @var Updater $updater */
$updater = $objectManager->get(Updater::class);
$updater->update(
    $quoteItem,
    [
        'qty' => 1,
        'custom_price' => 12,
    ]
);
$quote->collectTotals();
$quoteResource->save($quote);
