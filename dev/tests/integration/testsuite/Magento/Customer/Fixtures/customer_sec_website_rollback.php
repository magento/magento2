<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Registry;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

$objectManager = Bootstrap::getObjectManager();
$registry = $objectManager->get(Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/** @var WebsiteRepositoryInterface $websiteRepository */
$websiteRepository = $objectManager->get(WebsiteRepositoryInterface::class);
$website = $websiteRepository->get('test');

/** @var $repository CustomerRepositoryInterface */
$repository = $objectManager->get(CustomerRepositoryInterface::class);
$customer = $repository->get('customer.web@example.com', $website->getId());
$repository->delete($customer);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);

Resolver::getInstance()->requireDataFixture('Magento/Store/_files/websites_different_countries_rollback.php');
