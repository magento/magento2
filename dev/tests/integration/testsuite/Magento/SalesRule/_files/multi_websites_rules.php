<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory;
use Magento\SalesRule\Model\ResourceModel\Rule as RuleResource;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Store/_files/website.php');
Resolver::getInstance()->requireDataFixture('Magento/SalesRule/_files/rules.php');

$objectManager = Bootstrap::getObjectManager();
/** @var WebsiteRepositoryInterface $websiteRepository */
$websiteRepository = $objectManager->get(WebsiteRepositoryInterface::class);
$website = $websiteRepository->get('test');
/** @var RuleResource $ruleResource */
$ruleResource = $objectManager->get(RuleResource::class);
$rule2 = $objectManager->get(CollectionFactory::class)->create()
    ->addFieldToFilter('name', '#2')
    ->getFirstItem();
$rule3 = $objectManager->get(CollectionFactory::class)->create()
    ->addFieldToFilter('name', '#3')
    ->getFirstItem();
$rule2->setWebsiteIds($website->getId());
$rule3->setWebsiteIds(implode(',', [1, $website->getId()]));
$ruleResource->save($rule2);
$ruleResource->save($rule3);
