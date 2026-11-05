<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogInventoryGraphQl\Test\Unit\Model\Resolver;

use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Api\Data\StockStatusInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Model\StockRegistryPreloader;
use Magento\CatalogInventoryGraphQl\Model\Resolver\StockStatus;
use Magento\CatalogInventoryGraphQl\Model\Resolver\StockStatusProvider;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ResolveRequest;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\Quote\Model\Quote\Item;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Magento\CatalogInventoryGraphQl\Model\Resolver\StockStatus
 */
class StockStatusTest extends TestCase
{
    /**
     * @var StockStatus
     */
    private $resolver;

    /**
     * @var StockRegistryPreloader|MockObject
     */
    private $preloaderMock;

    /**
     * @var StockStatusProvider|MockObject
     */
    private $stockStatusProviderMock;

    /**
     * @var ContextInterface|MockObject
     */
    private $contextMock;

    /**
     * @var Field|MockObject
     */
    private $fieldMock;

    /**
     * @var ResolveInfo|MockObject
     */
    private $resolveInfoMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->preloaderMock = $this->createMock(StockRegistryPreloader::class);
        $this->stockStatusProviderMock = $this->createMock(StockStatusProvider::class);
        $this->contextMock = $this->createMock(ContextInterface::class);
        $this->fieldMock = $this->createMock(Field::class);
        $this->resolveInfoMock = $this->createMock(ResolveInfo::class);

        $stockConfigurationMock = $this->createMock(StockConfigurationInterface::class);
        $stockConfigurationMock->method('getDefaultScopeId')->willReturn(0);

        $this->resolver = new StockStatus(
            $this->preloaderMock,
            $stockConfigurationMock,
            $this->stockStatusProviderMock
        );
    }

    public function testAllProductsArePreloadedWithASingleCall()
    {
        $requests = [
            $this->createRequest(['model' => $this->createProduct(1)]),
            $this->createRequest(['model' => $this->createProduct(2)]),
            $this->createRequest(['model' => $this->createProduct(3)]),
        ];

        $this->preloaderMock->expects($this->once())
            ->method('preloadStockStatuses')
            ->with([1, 2, 3], 0)
            ->willReturn([
                $this->createStockStatus(1, 1),
                $this->createStockStatus(2, 0),
            ]);
        $this->stockStatusProviderMock->expects($this->never())->method('resolve');

        $response = $this->resolver->resolve($this->contextMock, $this->fieldMock, $requests);

        $this->assertEquals('IN_STOCK', $response->findResponseFor($requests[0]));
        $this->assertEquals('OUT_OF_STOCK', $response->findResponseFor($requests[1]));
        $this->assertEquals('OUT_OF_STOCK', $response->findResponseFor($requests[2]));
    }

    public function testCartItemRequestsAreDelegated()
    {
        $cartItemValue = ['model' => $this->createProduct(1), 'cart_item' => $this->createMock(Item::class)];
        $requests = [
            $this->createRequest($cartItemValue),
            $this->createRequest(['model' => $this->createProduct(2)]),
        ];

        $this->preloaderMock->expects($this->once())
            ->method('preloadStockStatuses')
            ->with([2], 0)
            ->willReturn([$this->createStockStatus(2, 1)]);
        $this->stockStatusProviderMock->expects($this->once())
            ->method('resolve')
            ->with($this->fieldMock, $this->contextMock, $this->resolveInfoMock, $cartItemValue, null)
            ->willReturn('OUT_OF_STOCK');

        $response = $this->resolver->resolve($this->contextMock, $this->fieldMock, $requests);

        $this->assertEquals('OUT_OF_STOCK', $response->findResponseFor($requests[0]));
        $this->assertEquals('IN_STOCK', $response->findResponseFor($requests[1]));
    }

    public function testCartItemOnlyBatchDoesNotPreload()
    {
        $requests = [
            $this->createRequest(['model' => $this->createProduct(1), 'cart_item' => $this->createMock(Item::class)]),
        ];

        $this->preloaderMock->expects($this->never())->method('preloadStockStatuses');
        $this->stockStatusProviderMock->expects($this->once())->method('resolve')->willReturn('IN_STOCK');

        $response = $this->resolver->resolve($this->contextMock, $this->fieldMock, $requests);

        $this->assertEquals('IN_STOCK', $response->findResponseFor($requests[0]));
    }

    public function testMissingModelIsRejected()
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('"model" value should be specified');

        $this->resolver->resolve($this->contextMock, $this->fieldMock, [$this->createRequest([])]);
    }

    /**
     * @param array $value
     * @return ResolveRequest
     */
    private function createRequest(array $value): ResolveRequest
    {
        return new ResolveRequest($this->fieldMock, $this->contextMock, $this->resolveInfoMock, $value, null);
    }

    /**
     * @param int $id
     * @return Product|MockObject
     */
    private function createProduct(int $id)
    {
        $product = $this->createMock(Product::class);
        $product->method('getId')->willReturn($id);

        return $product;
    }

    /**
     * @param int $productId
     * @param int $status
     * @return StockStatusInterface|MockObject
     */
    private function createStockStatus(int $productId, int $status)
    {
        $stockStatus = $this->createMock(StockStatusInterface::class);
        $stockStatus->method('getProductId')->willReturn($productId);
        $stockStatus->method('getStockStatus')->willReturn($status);

        return $stockStatus;
    }
}
