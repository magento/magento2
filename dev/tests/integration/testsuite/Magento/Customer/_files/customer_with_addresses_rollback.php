<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;

$objectManager = Bootstrap::getObjectManager();
/** @var Registry $registry */
$registry = $objectManager->get(Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);
/** @var CustomerRepositoryInterface $customerRepo */
$customerRepo = $objectManager->get(CustomerRepositoryInterface::class);
try {
    $customer = $customerRepo->get('customer_with_addresses@test.com');
    /** @var AddressRepositoryInterface $addressRepo */
    $addressRepo = $objectManager->get(AddressRepositoryInterface::class);
    foreach ($customer->getAddresses() as $address) {
        $addressRepo->delete($address);
    }
    $customerRepo->delete($customer);
} catch (NoSuchEntityException $exception) {
    //Already deleted
}
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
