<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

use Magento\TestFramework\Helper\Bootstrap;
use Magento\User\Model\UserFactory;
use Magento\User\Model\User;

/** @var \Magento\Framework\ObjectManagerInterface $objectManager */
$objectManager = Bootstrap::getObjectManager();
$userFactory = $objectManager->get(UserFactory::class);
$userNames = ['adminUserNotExpired', 'adminUserExpired'];

foreach ($userNames as $userName) {
    /** @var User $user */
    $user = $userFactory->create();
    $user->load($userName, 'username');

    if ($user->getId() !== null) {
        $user->delete();
    }
}
