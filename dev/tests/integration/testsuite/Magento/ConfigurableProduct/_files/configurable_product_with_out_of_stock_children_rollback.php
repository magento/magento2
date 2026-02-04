<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\ConfigurableProduct\Model\DeleteConfigurableProduct;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

/** @var ObjectManagerInterface $objectManager */
$objectManager = Bootstrap::getObjectManager();
/** @var DeleteConfigurableProduct $deleteConfigurableProductService */
$deleteConfigurableProductService = $objectManager->get(DeleteConfigurableProduct::class);
$deleteConfigurableProductService->execute('configurable');

Resolver::getInstance()->requireDataFixture('Magento/ConfigurableProduct/_files/configurable_attribute_rollback.php');
Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/category_rollback.php');
