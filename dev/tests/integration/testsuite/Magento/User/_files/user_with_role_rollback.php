<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\TestFramework\Helper\Bootstrap;
use Magento\User\Model\User;

/** @var $model \Magento\User\Model\User */
$model = Bootstrap::getObjectManager()->create(User::class);
$user = $model->loadByUsername('adminUser');
if ($user->getId()) {
    $model->delete();
}
