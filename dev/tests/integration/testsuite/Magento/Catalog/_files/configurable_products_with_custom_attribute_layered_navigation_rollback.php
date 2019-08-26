<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

// phpcs:ignore Magento2.Security.IncludeFile
require __DIR__ . '/../../ConfigurableProduct/_files/configurable_products_rollback.php';

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Eav\Api\AttributeRepositoryInterface;

$eavConfig = Bootstrap::getObjectManager()->get(\Magento\Eav\Model\Config::class);

/** @var AttributeRepositoryInterface $attributeRepository */
$attributeRepository = Bootstrap::getObjectManager()->get(AttributeRepositoryInterface::class);
/** @var \Magento\Eav\Api\Data\AttributeInterface $attribute */
$attribute = $attributeRepository->get('catalog_product', 'test_configurable');
$attributeRepository->delete($attribute);
