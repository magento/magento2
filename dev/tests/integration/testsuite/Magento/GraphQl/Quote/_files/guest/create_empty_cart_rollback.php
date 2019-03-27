<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\TestFramework\Helper\Bootstrap;

/** @var QuoteFactory $quoteFactory */
$quoteFactory = Bootstrap::getObjectManager()->get(QuoteFactory::class);
/** @var QuoteResource $quoteResource */
$quoteResource = Bootstrap::getObjectManager()->get(QuoteResource::class);
/** @var QuoteIdMaskFactory $quoteIdMaskFactory */
$quoteIdMaskFactory = Bootstrap::getObjectManager()->get(QuoteIdMaskFactory::class);

$quote = $quoteFactory->create();
$quoteResource->load($quote, 'test_quote', 'reserved_order_id');
$quoteResource->delete($quote);

/** @var QuoteIdMask $quoteIdMask */
$quoteIdMask = $quoteIdMaskFactory->create();
$quoteIdMask->setQuoteId($quote->getId())
    ->delete();
