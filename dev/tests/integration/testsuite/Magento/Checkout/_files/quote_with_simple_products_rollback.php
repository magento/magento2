<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;
use Magento\Quote\Model\ResourceModel\Quote\Collection;
use Magento\Quote\Model\Quote;
use Magento\Framework\Registry;

/** @var Registry $registry */
$registry = Bootstrap::getObjectManager()->get(Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/** @var $order Quote */
$quoteCollection = Bootstrap::getObjectManager()->create(Collection::class);
foreach ($quoteCollection as $quote) {
    $quote->delete();
}

Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/multiple_products_rollback.php');

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
