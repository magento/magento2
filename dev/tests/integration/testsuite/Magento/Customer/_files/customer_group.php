<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
/** @var \Magento\Customer\Api\GroupRepositoryInterface $groupRepository */
$groupRepository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    \Magento\Customer\Api\GroupRepositoryInterface::class
);

$groupFactory = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    \Magento\Customer\Api\Data\GroupInterfaceFactory::class
);
$groupDataObject = $groupFactory->create();
$groupDataObject->setCode('custom_group')->setTaxClassId(3);
$groupRepository->save($groupDataObject);
