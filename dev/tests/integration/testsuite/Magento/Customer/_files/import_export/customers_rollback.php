<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Customer\Model\Customer;

/** @var $objectManager ObjectManagerInterface */
$objectManager = Bootstrap::getObjectManager();

/** @var $registry Registry */
$registry = $objectManager->get(Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/** @var $customer Customer */
$customer = $objectManager->create(Customer::class);

$emailsToDelete = [
    'customer@example.com',
    'julie.worrell@example.com',
    'david.lamar@example.com',
];
foreach ($emailsToDelete as $email) {
    try {
        $customer->loadByEmail($email)->delete();
    } catch (\Exception $e) {
    }
}
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
$registry->unregister('_fixture/Magento_ImportExport_Customer_Collection');
