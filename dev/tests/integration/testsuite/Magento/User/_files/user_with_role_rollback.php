<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
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
