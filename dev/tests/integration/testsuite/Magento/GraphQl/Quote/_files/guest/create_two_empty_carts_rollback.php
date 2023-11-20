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
$quote1 = $quoteFactory->create();
$quoteResource->load($quote1, 'test_quote1', 'reserved_order_id');
$quoteResource->delete($quote1);
/** @var QuoteIdMask $quoteIdMask1 */
$quoteIdMask1 = $quoteIdMaskFactory->create();
$quoteIdMask1->setQuoteId($quote1->getId())->delete();
$quote2 = $quoteFactory->create();
$quoteResource->load($quote2, 'test_quote2', 'reserved_order_id');
$quoteResource->delete($quote2);
/** @var QuoteIdMask $quoteIdMask2 */
$quoteIdMask2 = $quoteIdMaskFactory->create();
$quoteIdMask2->setQuoteId($quote2->getId())->delete();
