<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Action;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\TestFramework\Helper\Bootstrap;

/** @var ProductRepositoryInterface $productRepository */
$productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);

try {
    /** @var Product $configurableProduct */
    $configurableProduct = $productRepository->get('configurable');
    /** @var Configurable $productTypeInstance */
    $productTypeInstance = $configurableProduct->getTypeInstance();
    /** @var Product $child */
    foreach ($productTypeInstance->getUsedProducts($configurableProduct) as $child) {

        $productAction = Bootstrap::getObjectManager()->get(Action::class);
        $productAction->updateAttributes(
            [$child->getId()],
            [ProductAttributeInterface::CODE_STATUS => Status::STATUS_DISABLED],
            $child->getStoreId()
        );
        break;
    }
} catch (Exception $e) {
    // Nothing to remove
}
