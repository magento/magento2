<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Customer/_files/three_customers_rollback.php');

$objectManager = Bootstrap::getObjectManager();
$subscriberCollection = $objectManager->get(\Magento\Newsletter\Model\ResourceModel\Subscriber\Collection::class);
foreach ($subscriberCollection as $subscriber) {
    /** @var Magento\Newsletter\Model\Subscriber $subscriber */
    $subscriber->delete();
}
