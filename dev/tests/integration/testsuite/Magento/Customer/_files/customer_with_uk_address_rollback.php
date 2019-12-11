<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
/** @var Registry $registry */
$registry = $objectManager->get(Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);
/** @var CustomerRepositoryInterface $customerRepository */
$customerRepository = $objectManager->get(CustomerRepositoryInterface::class);

try {
    $customer = $customerRepository->get('customer_uk_address@test.com');
    /** @var AddressRepositoryInterface $addressRepository */
    $addressRepository = $objectManager->create(AddressRepositoryInterface::class);

    foreach ($customer->getAddresses() as $address) {
        $addressRepository->delete($address);
    }

    $customerRepository->delete($customer);
} catch (NoSuchEntityException $exception) {
    //Already deleted
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
