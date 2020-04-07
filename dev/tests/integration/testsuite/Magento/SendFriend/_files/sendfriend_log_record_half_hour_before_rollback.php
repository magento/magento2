<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\SendFriend\Model\DeleteLogRowsByIp;

/** @var DeleteLogRowsByIp $deleteLogRowsByIp */
$deleteLogRowsByIp = Bootstrap::getObjectManager()->get(DeleteLogRowsByIp::class);
$deleteLogRowsByIp->execute('127.0.0.1');
