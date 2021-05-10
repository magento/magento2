<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/product_simple_duplicated_rollback.php');
Resolver::getInstance()->requireDataFixture('Magento/Customer/_files/customer_rollback.php');

/** @var \Magento\Framework\Registry $registry */
$registry = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(\Magento\Framework\Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$customerRepository = $objectManager->create(CustomerRepositoryInterface::class);
$customer = $customerRepository->get('customer@wishlist.com');
$customerRepository->delete($customer);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
