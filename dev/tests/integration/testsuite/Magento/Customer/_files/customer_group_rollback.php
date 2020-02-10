<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Customer\Api\Data\GroupInterface;

/** @var ObjectManagerInterface $objectManager */
$objectManager = Bootstrap::getObjectManager();
$groupRepository = $objectManager->get(GroupRepositoryInterface::class);
/** @var SearchCriteriaBuilder $searchBuilder */
$searchBuilder = $objectManager->get(SearchCriteriaBuilder::class);
$searchCriteria = $searchBuilder->addFilter(GroupInterface::CODE, 'custom_group')
    ->create();
$groups = $groupRepository->getList($searchCriteria)
    ->getItems();
foreach ($groups as $group) {
    try {
        $groupRepository->delete($group);
    } catch (NoSuchEntityException $exception) {
        //Group already removed
    }
}
