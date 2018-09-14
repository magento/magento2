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
$quote = $objectManager->create(Quote::class);
$quote->load('quote123', 'reserved_order_id');
if ($quote->getId()) {
    $quote->delete();
}
