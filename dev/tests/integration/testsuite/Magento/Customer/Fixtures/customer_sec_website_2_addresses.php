<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\TestFramework\Helper\Bootstrap;

require __DIR__ . '/customer_sec_website.php';

$objectManager = Bootstrap::getObjectManager();

$addressData = include __DIR__ . '/address_data.php';

/** @var AddressRepositoryInterface $repository */
$repository = $objectManager->get(AddressRepositoryInterface::class);
foreach ($addressData as $data) {
    /** @var AddressInterface $address */
    $address = $objectManager->create(AddressInterface::class, ['data' => $data]);
    $address->setCustomerId($customer->getId());
    $repository->save($address);
}
