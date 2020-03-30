<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Integration\Api\CustomerTokenServiceInterface;

require __DIR__ . '/../../../Magento/Customer/_files/customer.php';

/** @var CustomerTokenServiceInterface $customerTokenService */
$customerTokenService = $objectManager->get(CustomerTokenServiceInterface::class);

$customerTokenService->createCustomerAccessToken($customer->getEmail(), $customer->getPassword());
