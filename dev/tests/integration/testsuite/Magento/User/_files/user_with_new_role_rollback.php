<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

use Magento\Authorization\Model\Role;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\User\Model\User;

$objectManager = Bootstrap::getObjectManager();
/** @var User $user */
$user = $objectManager->create(User::class);
$user->load('admin_with_role', 'username');
$user->delete();
