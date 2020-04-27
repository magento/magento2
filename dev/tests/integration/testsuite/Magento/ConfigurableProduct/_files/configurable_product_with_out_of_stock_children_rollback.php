<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\ConfigurableProduct\Model\DeleteConfigurableProduct;
use Magento\TestFramework\Helper\Bootstrap;

/** @var ObjectManagerInterface $objectManager */
$objectManager = Bootstrap::getObjectManager();
/** @var DeleteConfigurableProduct $deleteConfigurableProductService */
$deleteConfigurableProductService = $objectManager->get(DeleteConfigurableProduct::class);
$deleteConfigurableProductService->execute('configurable');

require __DIR__ . '/configurable_attribute_rollback.php';
require __DIR__ . '/../../Catalog/_files/category_rollback.php';
