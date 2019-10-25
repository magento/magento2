<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

$pageRepository = $objectManager->get(\Magento\Cms\Api\PageRepositoryInterface::class);

$searchCriteriaBuilder = $objectManager->get(\Magento\Framework\Api\SearchCriteriaBuilder::class);
$searchCriteria = $searchCriteriaBuilder->addFilter('identifier', 'page_default_store')
    ->create();
$result = $pageRepository->getList($searchCriteria);
foreach ($result->getItems() as $item) {
    $pageRepository->delete($item);
}
