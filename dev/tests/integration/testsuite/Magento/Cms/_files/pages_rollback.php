<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

/** @var PageRepositoryInterface $pageRepository */
$pageRepository = $objectManager->get(PageRepositoryInterface::class);

/** @var SearchCriteriaBuilder $searchCriteriaBuilder */
$searchCriteriaBuilder = $objectManager->get(SearchCriteriaBuilder::class);
$searchCriteria = $searchCriteriaBuilder->addFilter(PageInterface::IDENTIFIER, ['page100', 'page_design_blank'], 'in')
    ->create();
$result = $pageRepository->getList($searchCriteria);

/**
 * Tests which are wrapped with MySQL transaction clear all data by transaction rollback.
 * In that case there is "if" which checks that "page100" and "page_design_blank" still exists in database.
 */
foreach ($result->getItems() as $item) {
    $pageRepository->delete($item);
}
