<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/** @var \Magento\Customer\Api\GroupRepositoryInterface $groupRepository */
$groupRepository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    'Magento\Customer\Api\GroupRepositoryInterface'
);

$groupFactory = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    'Magento\Customer\Api\Data\GroupInterfaceFactory'
);
$groupDataObject = $groupFactory->create();
$groupDataObject->setCode('custom_group')->setTaxClassId(3);
$groupRepository->save($groupDataObject);
