<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Quote\Model\Quote;

/** @var $objectManager \Magento\TestFramework\ObjectManager */
$objectManager = Bootstrap::getObjectManager();
/** @var Quote $quote */
$quoteRepository = $objectManager->create(Quote::class);
$quoteRepository->load('quote123', 'reserved_order_id');
$quoteRepository->delete();
