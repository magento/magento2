<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
<<<<<<< HEAD
=======
declare(strict_types=1);

>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\ObjectManagerInterface;
use Magento\SalesRule\Api\Data\RuleInterface;
use Magento\SalesRule\Api\RuleRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;

/** @var ObjectManagerInterface $objectManager */
$objectManager = Bootstrap::getObjectManager();
<<<<<<< HEAD

/** @var \Magento\Framework\Registry $registry */
$registry = $objectManager->get(\Magento\Framework\Registry::class);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

=======
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
/** @var RuleRepositoryInterface $ruleRepository */
$ruleRepository = $objectManager->get(RuleRepositoryInterface::class);
/** @var SearchCriteriaBuilder $searchCriteriaBuilder */
$searchCriteriaBuilder = $objectManager->get(SearchCriteriaBuilder::class);
<<<<<<< HEAD
$searchCriteria = $searchCriteriaBuilder->addFilter('name', '10$ discount')
    ->create();
$items = $ruleRepository->getList($searchCriteria)
    ->getItems();
=======
$searchCriteria = $searchCriteriaBuilder->addFilter('name', '10$ discount')->create();
$items = $ruleRepository->getList($searchCriteria)->getItems();
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc

/** @var RuleInterface $item */
foreach ($items as $item) {
    $ruleRepository->deleteById($item->getRuleId());
}
<<<<<<< HEAD

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
=======
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
