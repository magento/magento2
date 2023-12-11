<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Test\Unit\Plugin\Model\ResourceModel;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Product as ProductResourceModel;
use Magento\Wishlist\Model\WishlistCleaner;
use Magento\Wishlist\Plugin\Model\ResourceModel\Product as Plugin;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests product delete observer
 */
class ProductTest extends TestCase
{
    /**
     * @var Plugin
     */
    private $model;

    /**
     * @var MockObject|WishlistCleaner
     */
    private $wishlistCleaner;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->wishlistCleaner = $this->createMock(WishlistCleaner::class);
        $this->model = new Plugin($this->wishlistCleaner);
    }

    /**
     * Asserts that item option cleaner is executed when product is deleted
     *
     * @return void
     */
    public function testExecute()
    {
        $product = $this->getMockForAbstractClass(ProductInterface::class);
        $productResourceModel = $this->createMock(ProductResourceModel::class);
        $this->wishlistCleaner->expects($this->once())->method('execute')->with($product);
        $this->model->beforeDelete($productResourceModel, $product);
    }
}
