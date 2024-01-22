<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProductSales\Test\Unit\ViewModel;

use Magento\ConfigurableProductSales\ViewModel\ItemRendererTypeResolver;
use Magento\Framework\DataObject;
use Magento\Sales\Model\Order\Item;
use PHPUnit\Framework\TestCase;

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
     * @dataProvider resolveConfigurableOrderItemDataProvider
     */
    public function testResolveConfigurableOrderItem(?string $realProductType, string $expectedProductType): void
    {
        $orderItem = $this->getOrderItemMock();
        $orderItem->setProductType('configurable');
        $childOrderItem = $this->getOrderItemMock();
        $childOrderItem->setProductOptions(['real_product_type' => $realProductType]);
        $orderItem->addChildItem($childOrderItem);
        $this->assertEquals($expectedProductType, $this->model->resolve($orderItem));
        $this->assertEquals($expectedProductType, $this->model->resolve(new DataObject(['order_item' => $orderItem])));
    }

    /**
     * @return array
     */
    public function resolveConfigurableOrderItemDataProvider(): array
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
        $orderItem->setProductType('virtual');
        $this->assertEquals('virtual', $this->model->resolve($orderItem));
        $this->assertEquals('virtual', $this->model->resolve(new DataObject(['order_item' => $orderItem])));
    }

    /**
     * @return Item
     */
    private function getOrderItemMock(): Item
    {
        return $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
    }
}
