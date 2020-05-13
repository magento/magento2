<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Framework\Registry;
use Magento\Shipping\Model\Order\Track;
use Magento\Shipping\Model\ResourceModel\Order\Track\Collection;
use Magento\TestFramework\Helper\Bootstrap;

require __DIR__ . '/../../../Magento/Sales/_files/order_rollback.php';

/** @var Registry $registry */
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

$trackCollection = Bootstrap::getObjectManager()->create(Collection::class);
/** @var $track Track */
foreach ($trackCollection as $track) {
    $track->delete();
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
