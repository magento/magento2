<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Action;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/ConfigurableProduct/_files/configurable_product_two_websites.php');

$childSku = 'simple_Option_1';
$objectManager = Bootstrap::getObjectManager();
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->create(ProductRepositoryInterface::class);
$childProduct = $productRepository->get($childSku);
$productAction = Bootstrap::getObjectManager()->get(Action::class);
$productAction->updateAttributes(
    [$childProduct->getEntityId()],
    [ProductAttributeInterface::CODE_STATUS => Status::STATUS_DISABLED],
    $childProduct->getStoreId()
);
