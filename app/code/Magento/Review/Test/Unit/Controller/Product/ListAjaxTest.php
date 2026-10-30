<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Review\Test\Unit\Controller\Product;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Result\Layout;
use Magento\Review\Controller\Product\ListAjax;
use Magento\Review\Model\Review\Config;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
#[AllowMockObjectsWithoutExpectations]
class ListAjaxTest extends TestCase
{
    /**
     * A category id in the request that does not exist must not break the review list.
     */
    public function testExecuteIgnoresNonExistentCategory(): void
    {
        $categoryId = 999999;
        $productId = 1;

        /** @var Http|MockObject $request */
        $request = $this->createPartialMock(Http::class, ['getParam', 'isAjax']);
        $request->method('getParam')->willReturnMap([
            ['category', false, $categoryId],
            ['id', null, $productId],
        ]);
        $request->method('isAjax')->willReturn(true);

        $product = $this->createPartialMock(
            Product::class,
            ['isVisibleInCatalog', 'isVisibleInSiteVisibility', 'getWebsiteIds']
        );
        $product->method('isVisibleInCatalog')->willReturn(true);
        $product->method('isVisibleInSiteVisibility')->willReturn(true);
        $product->method('getWebsiteIds')->willReturn([1]);

        $store = $this->createPartialMock(Store::class, ['getWebsiteId']);
        $store->method('getWebsiteId')->willReturn(1);

        $storeManager = $this->createMock(StoreManagerInterface::class);
        $storeManager->method('getStore')->willReturn($store);

        $productRepository = $this->createMock(ProductRepositoryInterface::class);
        $productRepository->method('getById')->with($productId)->willReturn($product);

        $categoryRepository = $this->createMock(CategoryRepositoryInterface::class);
        $categoryRepository->expects($this->once())
            ->method('get')
            ->with($categoryId)
            ->willThrowException(new NoSuchEntityException());

        $coreRegistry = $this->createMock(Registry::class);
        $registeredKeys = [];
        $coreRegistry->method('register')->willReturnCallback(
            function ($key) use (&$registeredKeys) {
                $registeredKeys[] = $key;
                return null;
            }
        );

        /** @var Layout|MockObject $layout */
        $layout = $this->createMock(Layout::class);
        $resultFactory = $this->createMock(ResultFactory::class);
        $resultFactory->method('create')->with(ResultFactory::TYPE_LAYOUT)->willReturn($layout);

        $eventManager = $this->createMock(ManagerInterface::class);

        $objectManagerHelper = new ObjectManager($this);
        $context = $objectManagerHelper->getObject(
            Context::class,
            [
                'request' => $request,
                'resultFactory' => $resultFactory,
                'eventManager' => $eventManager,
            ]
        );
        $controller = $objectManagerHelper->getObject(
            ListAjax::class,
            [
                'context' => $context,
                'coreRegistry' => $coreRegistry,
                'categoryRepository' => $categoryRepository,
                'productRepository' => $productRepository,
                'storeManager' => $storeManager,
                'reviewsConfig' => $this->createMock(Config::class),
            ]
        );

        $this->assertSame($layout, $controller->execute());
        $this->assertContains('product', $registeredKeys);
        $this->assertNotContains('current_category', $registeredKeys);
    }
}
