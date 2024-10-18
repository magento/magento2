<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Registry;
use Magento\ProductAlert\Model\PriceFactory;
use Magento\ProductAlert\Model\StockFactory;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

$objectManager = Bootstrap::getObjectManager();
/** @var StockFactory $stockFactory */
$stockFactory = $objectManager->get(StockFactory::class);
/** @var PriceFactory $priceFactory */
$priceFactory = $objectManager->get(PriceFactory::Class);
/** @var CustomerRepositoryInterface $customerRepository */
$customerRepository = $objectManager->get(CustomerRepositoryInterface::class);
$customer = $customerRepository->get('customer@example.com');
/** @var Registry $registry */
$registry = $objectManager->get(Registry::class);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

$stockAlert = $stockFactory->create();
$stockAlert->deleteCustomer((int)$customer->getId());

$priceAlert = $priceFactory->create();
$priceAlert->deleteCustomer(($customer->getId()));

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);

Resolver::getInstance()->requireDataFixture('Magento/Customer/_files/customer_rollback.php');
Resolver::getInstance()->requireDataFixture('Magento/ProductAlert/_files/product_alert_rollback.php');
