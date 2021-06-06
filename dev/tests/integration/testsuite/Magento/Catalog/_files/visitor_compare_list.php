<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Model\Product\Compare\ListCompare;
use Magento\Catalog\Model\Product\Compare\ListCompareFactory;
use Magento\Customer\Model\Visitor;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/product_simple.php');

$objectManager = Bootstrap::getObjectManager();
/** @var Visitor $visitor */
$visitor = $objectManager->get(Visitor::class);
$visitor->setVisitorId(123);
/** @var ListCompare $compareList */
$compareList = $objectManager->get(ListCompareFactory::class)->create();
$compareList->addProduct(1);
