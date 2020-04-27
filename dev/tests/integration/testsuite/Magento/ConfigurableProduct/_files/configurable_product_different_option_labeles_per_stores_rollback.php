<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\TestFramework\ConfigurableProduct\Model\DeleteConfigurableProduct;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
/** @var DeleteConfigurableProduct $deleteConfigurableProduct */
$deleteConfigurableProduct = $objectManager->get(DeleteConfigurableProduct::class);
$deleteConfigurableProduct->execute('configurable');

require __DIR__ . '/configurable_attribute_different_labels_per_stores_rollback.php';
