<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Integration\Model\Oauth\Token\RequestThrottler;

/** @var $model \Magento\User\Model\User */
$model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\User\Model\User::class);
$userName = 'webapi_user';
$model->load($userName, 'username');
$model->delete();

/* Unlock account if it was locked */
/** @var RequestThrottler $throttler */
$throttler = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(RequestThrottler::class);
$throttler->resetAuthenticationFailuresCount($userName, RequestThrottler::USER_TYPE_ADMIN);
