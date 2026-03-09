<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Checkout\Test\Unit\Block\Cart\Item\Renderer\Actions;

use Magento\Catalog\Model\Product;
use Magento\Checkout\Block\Cart\Item\Renderer\Actions\Generic;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\Quote\Item;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;

class GenericTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var Generic
     */
    protected $model;

    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManager($this);

        $this->model = $objectManagerHelper->getObject(
            Generic::class,
            []
        );
    }

    public function testGetItem()
    {
        /**
         * @var Item|MockObject $itemMock
         */
        $itemMock = $this->createMock(Item::class);

        $this->assertEquals($this->model, $this->model->setItem($itemMock));
        $this->assertEquals($itemMock, $this->model->getItem());
    }

    public function testIsProductVisibleInSiteVisibility()
    {
        /**
         * @var Item|MockObject $itemMock
         */
        $itemMock = $this->createMock(Item::class);

        /**
         * @var Product|MockObject $productMock
         */
        $productMock = $this->createMock(Product::class);

        $itemMock->expects($this->once())
            ->method('getProduct')
            ->willReturn($productMock);

        $productMock->expects($this->once())
            ->method('isVisibleInSiteVisibility')
            ->willReturn(true);

        $this->assertEquals($this->model, $this->model->setItem($itemMock));
        $this->assertTrue($this->model->isProductVisibleInSiteVisibility());
    }

    public function testIsVirtual()
    {
        /**
         * @var Item|MockObject $itemMock
         */
        $itemMock = $this->createPartialMockWithReflection(Item::class, ['getIsVirtual']);
        $itemMock->method('getIsVirtual')->willReturn(true);

        $this->assertEquals($this->model, $this->model->setItem($itemMock));
        $this->assertTrue($this->model->isVirtual());
    }
}
