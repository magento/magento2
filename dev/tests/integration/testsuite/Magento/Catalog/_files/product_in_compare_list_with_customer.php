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

require __DIR__ . '/second_product_simple.php';
require __DIR__ . '/../../Customer/_files/customer.php';

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
