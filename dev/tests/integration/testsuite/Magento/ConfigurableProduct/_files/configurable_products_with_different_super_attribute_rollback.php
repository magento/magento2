<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;
use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;

Resolver::getInstance()->requireDataFixture('Magento/ConfigurableProduct/_files/configurable_products_rollback.php');

$objectManager = Bootstrap::getObjectManager();
/** @var Registry $registry */
$registry = $objectManager->get(Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

Resolver::getInstance()->requireDataFixture('Magento/ConfigurableProduct/_files/configurable_attribute_2_rollback.php');

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
