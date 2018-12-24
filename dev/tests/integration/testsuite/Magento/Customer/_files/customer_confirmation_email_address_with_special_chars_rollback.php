<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

include __DIR__ . '/customer_confirmation_config_enable_rollback.php';

use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Customer\Model\Customer;

/** @var Registry $registry */
$registry = Bootstrap::getObjectManager()->get(Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/** @var Customer $customer */
$customer = Bootstrap::getObjectManager()->create(Customer::class);
$customer->load(1);
if ($customer->getId()) {
    $customer->delete();
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
