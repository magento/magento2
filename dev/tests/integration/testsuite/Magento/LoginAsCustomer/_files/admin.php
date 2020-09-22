<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\User\Model\UserFactory;
use Magento\User\Model\ResourceModel\User as UserResource;

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var UserFactory $userFactory */
$userFactory = $objectManager->get(UserFactory::class);
/** @var UserResource $userResource */
$userResource = $objectManager->get(UserResource::class);

$adminInfo = [
    'username'  => 'TestAdmin1',
    'firstname' => 'test',
    'lastname'  => 'test',
    'email'     => 'testadmin1@gmail.com',
    'password'  =>'Zilker777',
    'interface_locale' => 'en_US',
    'is_active' => 1
];

$userModel = $userFactory->create();
try {
    $userModel->setData($adminInfo);
    $userModel->setRoleId(1);
    $userResource->save($userModel);
} catch (\Magento\Framework\Exception\AlreadyExistsException $e) {
}
