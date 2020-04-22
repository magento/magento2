<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Customer\Api\CustomerRepositoryInterface;

require __DIR__ . '/customers.php';
require __DIR__ . '/../../../Store/_files/website.php';

$objectManager = Bootstrap::getObjectManager();
$repository = $objectManager->create(CustomerRepositoryInterface::class);
$customer = $repository->get('customer@example.com');
$customer->setWebsiteId($website->getId());
$repository->save($customer);
