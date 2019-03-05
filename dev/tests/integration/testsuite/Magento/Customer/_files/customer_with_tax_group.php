<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

require __DIR__ . '/customer.php';

$customer->setGroupId(3); // 3 is a predefined retailer group
$customer->save();
