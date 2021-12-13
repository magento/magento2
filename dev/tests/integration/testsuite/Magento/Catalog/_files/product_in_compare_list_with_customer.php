<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Model\Product\Compare\ListCompare;
use Magento\Catalog\Model\Product\Compare\ListCompareFactory;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Visitor;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/second_product_simple.php');
Resolver::getInstance()->requireDataFixture('Magento/Customer/_files/customer.php');

$objectManager = Bootstrap::getObjectManager();
/** @var Session $session */
$session = $objectManager->get(Session::class);

try {
    $session->loginById(1);
    /** @var Visitor $visitor */
    $visitor = $objectManager->get(Visitor::class);
    $visitor->setVisitorId(1);
    /** @var ListCompare $compareList */
    $compareList = $objectManager->get(ListCompareFactory::class)->create();
    $compareList->addProduct(6);
} finally {
    $session->logout();
}
