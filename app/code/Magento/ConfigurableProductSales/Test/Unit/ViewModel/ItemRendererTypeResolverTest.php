<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProductSales\Test\Unit\ViewModel;

use Magento\ConfigurableProductSales\ViewModel\ItemRendererTypeResolver;
use Magento\Framework\DataObject;
use Magento\Sales\Model\Order\Item;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Test configurable order item renderer type resolver
 */
class ItemRendererTypeResolverTest extends TestCase
{
    /**
     * @var ItemRendererTypeResolver
     */
    private $model;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new ItemRendererTypeResolver();
    }

    /**
     * @param string|null $realProductType
     * @param string $expectedProductType
     */
    #[DataProvider('resolveConfigurableOrderItemDataProvider')]
    public function testResolveConfigurableOrderItem(?string $realProductType, string $expectedProductType): void
    {
        $orderItem = $this->getOrderItemMock();
        $orderItem->method('getProductType')->willReturn('configurable');

        $childOrderItem = $this->getOrderItemMock();
        $childOrderItem->method('getRealProductType')->willReturn($realProductType);

        $orderItem->method('getChildrenItems')->willReturn([$childOrderItem]);

        $this->assertEquals($expectedProductType, $this->model->resolve($orderItem));
        $this->assertEquals($expectedProductType, $this->model->resolve(new DataObject(['order_item' => $orderItem])));
    }

    /**
     * @return array
     */
    public static function resolveConfigurableOrderItemDataProvider(): array
    {
        return [
            ['simple', 'simple'],
            [null, 'configurable'],
        ];
    }

    /**
     * @return void
     */
    public function testResolveSimpleOrderItem(): void
    {
        $orderItem = $this->getOrderItemMock();
        $orderItem->method('getProductType')->willReturn('virtual');
        $this->assertEquals('virtual', $this->model->resolve($orderItem));
        $this->assertEquals('virtual', $this->model->resolve(new DataObject(['order_item' => $orderItem])));
    }

    /**
     * @return Item
     */
    private function getOrderItemMock(): Item
    {
        return $this->createMock(Item::class);
    }
}
