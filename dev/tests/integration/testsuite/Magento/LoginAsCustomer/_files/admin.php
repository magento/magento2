<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\User\Model\UserFactory;

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var UserFactory $userFactory */
$userFactory = $objectManager->get(UserFactory::class);
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
    $userModel->save();
} catch (\Magento\Framework\Exception\AlreadyExistsException $e) {

}
