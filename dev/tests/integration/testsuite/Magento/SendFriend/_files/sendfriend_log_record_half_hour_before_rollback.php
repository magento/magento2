<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\SendFriend\Model\DeleteLogRowsByIp;

/** @var DeleteLogRowsByIp $deleteLogRowsByIp */
$deleteLogRowsByIp = Bootstrap::getObjectManager()->get(DeleteLogRowsByIp::class);
$deleteLogRowsByIp->execute('127.0.0.1');
