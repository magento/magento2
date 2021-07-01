<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Backend\App\Area\FrontNameResolver;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\User\Model\ResourceModel\User as UserResource;
use Magento\User\Model\UserFactory;

Bootstrap::getInstance()->loadArea(FrontNameResolver::AREA_CODE);
$objectManager = Bootstrap::getObjectManager();
/** @var UserFactory $userFactory */
$userFactory = $objectManager->create(UserFactory::class);
/** @var UserResource $userResource */
$userResource = $objectManager->create(UserResource::class);
$user = $userFactory->create(['data' =>  [
    "username" => "test_user",
    "firstname" => "Test",
    "lastname" => "Dev",
    "email" => "de@de.de",
    "password" => "admin123",
    "password_confirmation" => "admin123",
    "interface_locale" => "en_US",
    "is_active" => 1,
    "limit" => "20",
    "page" => "1",
    "assigned_user_role" => "",
    "role_name" => "",
    "extra" => null,
]]);
$user->setHasDataChanges(true);
$userResource->save($user);
$user->getId();
