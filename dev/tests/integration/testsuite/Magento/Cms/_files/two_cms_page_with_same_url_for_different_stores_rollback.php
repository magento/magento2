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
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

$objectManager = Bootstrap::getObjectManager();

/** @var PageRepositoryInterface $pageRepository */
$pageRepository = $objectManager->get(PageRepositoryInterface::class);

/** @var SearchCriteriaBuilder $searchCriteriaBuilder */
$searchCriteriaBuilder = $objectManager->get(SearchCriteriaBuilder::class);
$searchCriteria = $searchCriteriaBuilder->addFilter(PageInterface::IDENTIFIER, 'page1')
    ->create();
$result = $pageRepository->getList($searchCriteria);

foreach ($result->getItems() as $item) {
    $pageRepository->delete($item);
}

Resolver::getInstance()->requireDataFixture('Magento/Store/_files/second_store_rollback.php');
