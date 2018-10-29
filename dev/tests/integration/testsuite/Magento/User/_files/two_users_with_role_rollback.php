<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\TestFramework\Helper\Bootstrap;
use Magento\User\Model\User;

/**
 * Create an admin user with an assigned role
 */

/** @var $user User */
$user = Bootstrap::getObjectManager()->create(User::class);
$user->loadByUsername('johnAdmin')->delete();

/** @var $user User */
$user = Bootstrap::getObjectManager()->create(User::class);
$user->loadByUsername('annAdmin')->delete();
