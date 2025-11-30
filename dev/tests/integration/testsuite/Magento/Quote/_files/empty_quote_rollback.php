<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
/** @var $objectManager \Magento\TestFramework\ObjectManager */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$quote = $objectManager->create(\Magento\Quote\Model\Quote::class);
$quote->load('reserved_order_id', 'reserved_order_id')
    ->delete();

$objectManager->create(\Magento\Quote\Model\QuoteIdMask::class)
    ->delete($quote->getId());
