<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Authorization\Model\Acl\Role\Group as RoleGroup;
use Magento\Authorization\Model\ResourceModel\Role as RoleResource;
use Magento\Authorization\Model\RoleFactory;
use Magento\Authorization\Model\RulesFactory;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Backend\App\Area\FrontNameResolver;
use Magento\TestFramework\Helper\Bootstrap;

Bootstrap::getInstance()->loadArea(FrontNameResolver::AREA_CODE);
$objectManager = Bootstrap::getObjectManager();
/** @var RoleResource $roleResource */
$roleResource = $objectManager->get(RoleResource::class);
/** @var RoleFactory $roleFactory */
$roleFactory = $objectManager->get(RoleFactory::class);
$role = $roleFactory->create();
$role->setName('test_role')
    ->setPid(0)
    ->setRoleType(RoleGroup::ROLE_TYPE)
    ->setUserType(UserContextInterface::USER_TYPE_ADMIN);

$roleResource->save($role);
$ruleFactory = $objectManager->get(RulesFactory::class);
$ruleFactory->create()->setRoleId($role->getId())->setResources(['Magento_Backend::all'])->saveRel();
