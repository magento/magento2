<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\User\Model\UserFactory;

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var UserFactory $userFactory */
$userFactory = $objectManager->get(UserFactory::class);

$userModel = $userFactory->create();
$userModel->loadByUsername('TestAdmin1');
if ($userModel->getId()) {
    $userModel->delete();
}
