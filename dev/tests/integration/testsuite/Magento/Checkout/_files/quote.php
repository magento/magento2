<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */

$quote = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Quote\Model\Quote::class);
$quote->setData(['store_id' => 1, 'is_active' => 0, 'is_multi_shipping' => 0]);
$quote->save();
