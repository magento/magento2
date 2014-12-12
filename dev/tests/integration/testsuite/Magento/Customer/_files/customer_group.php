<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
