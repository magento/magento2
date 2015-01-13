<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/** @var \Magento\Customer\Api\GroupRepositoryInterface $groupRepository */
$groupRepository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    'Magento\Customer\Api\GroupRepositoryInterface'
);

$groupBuilder = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    'Magento\Customer\Api\Data\GroupDataBuilder'
);
$groupBuilder->setCode('custom_group')->setTaxClassId(3);
$groupRepository->save($groupBuilder->create());
