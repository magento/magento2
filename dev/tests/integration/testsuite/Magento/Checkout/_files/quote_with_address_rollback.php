<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

require __DIR__ . '/../../../Magento/Catalog/_files/products_rollback.php';
require __DIR__ . '/../../Customer/_files/customer_address_rollback.php';
require __DIR__ . '/../../Customer/_files/customer_rollback.php';

/** @var \Magento\Quote\Model\Quote $quote */
$quote = $objectManager->create(\Magento\Quote\Model\Quote::class);
$quote->load('test_order_1', 'reserved_order_id');
if ($quote->getId()) {
    $quote->delete();
}
