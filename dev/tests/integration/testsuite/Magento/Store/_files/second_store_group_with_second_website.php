<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Store/_files/website.php');

$objectManager = Bootstrap::getObjectManager();
/** @var WebsiteRepositoryInterface $websiteRepository */
$websiteRepository = $objectManager->get(WebsiteRepositoryInterface::class);
$website = $websiteRepository->get('test');

/**
 * @var \Magento\Store\Model\Group $storeGroup
 */
$storeGroup = $objectManager->create(\Magento\Store\Model\Group::class);
$storeGroup->setCode('some_group')
    ->setName('custom store group')
    ->setWebsite($website);
$storeGroup->save($storeGroup);

/* Refresh stores memory cache */
$objectManager->get(\Magento\Store\Model\StoreManagerInterface::class)->reinitStores();
