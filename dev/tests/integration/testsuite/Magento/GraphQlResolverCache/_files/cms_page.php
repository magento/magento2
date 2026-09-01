<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Cms\Model\PageFactory;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
$page = $objectManager->get(PageFactory::class)->create();
$page->setIdentifier('graphql_cache_page')
    ->setTitle('GraphQL Cache Page')
    ->setContent('<p>GraphQL cache page content</p>')
    ->setIsActive(true)
    ->setPageLayout('1column')
    ->setStores([0]);
$objectManager->get(PageRepositoryInterface::class)->save($page);
