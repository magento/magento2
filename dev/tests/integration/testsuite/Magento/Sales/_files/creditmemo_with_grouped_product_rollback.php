<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\ResourceModel\Order\Creditmemo\Collection;
use Magento\TestFramework\Helper\Bootstrap;

require 'default_rollback.php';

/** @var $creditmemo Creditmemo */
$creditmemoCollection = Bootstrap::getObjectManager()->create(Collection::class);
foreach ($creditmemoCollection as $creditmemo) {
    $creditmemo->delete();
}
