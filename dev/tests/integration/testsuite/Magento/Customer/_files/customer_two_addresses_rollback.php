<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;

/** @var Registry $registry */
$registry = Bootstrap::getObjectManager()->get(Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/** @var AddressRepositoryInterface $addressRepository */
$addressRepository = Bootstrap::getObjectManager()->get(AddressRepositoryInterface::class);

foreach ([1, 2] as $addressId) {
    try {
        $addressRepository->deleteById($addressId);
    } catch (NoSuchEntityException $e) {
        /**
         * Tests which are wrapped with MySQL transaction clear all data by transaction rollback.
         */
    }
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
