<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;

include __DIR__ . '../../../../../../dev/tests/integration/testsuite/Magento/Customer/_files/customer_from_repository.php';

$customerRepository = Bootstrap::getObjectManager()->get(CustomerRepositoryInterface::class);
$customer->getExtensionAttributes()->getExtraAbilities()[0]->setIsAllowedAddDescription(1);
$customerRepository->save($customer);
