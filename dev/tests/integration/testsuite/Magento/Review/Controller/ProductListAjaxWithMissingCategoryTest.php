<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Review\Controller;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\TestCase\AbstractController;

/**
 * A review list request carrying a category id that does not exist must still render.
 */
#[AppArea('frontend')]
class ProductListAjaxWithMissingCategoryTest extends AbstractController
{
    #[DataFixture('Magento/Catalog/_files/products.php')]
    public function testListAjaxIgnoresMissingCategory(): void
    {
        $product = $this->_objectManager->get(ProductRepositoryInterface::class)
            ->get('custom-design-simple-product');

        $this->getRequest()->setParam('id', $product->getId());
        $this->getRequest()->setParam('category', 999999);
        $this->getRequest()->setParam('isAjax', true);
        $this->dispatch('review/product/listAjax');

        $this->assertSame(200, $this->getResponse()->getHttpResponseCode());
    }
}
