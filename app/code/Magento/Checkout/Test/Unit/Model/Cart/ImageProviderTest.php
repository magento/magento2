<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Unit\Model\Cart;

class ImageProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Checkout\Model\Cart\ImageProvider
     */
    public $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Quote\Api\CartItemRepositoryInterface
     */
    protected $itemRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Checkout\CustomerData\ItemPoolInterface
     */
    protected $itemPoolMock;

    protected function setUp()
    {
        $this->itemRepositoryMock = $this->createMock(\Magento\Quote\Api\CartItemRepositoryInterface::class);
        $this->itemPoolMock = $this->createMock(\Magento\Checkout\CustomerData\ItemPoolInterface::class);
        $this->model = new \Magento\Checkout\Model\Cart\ImageProvider(
            $this->itemRepositoryMock,
            $this->itemPoolMock
        );
    }

    public function testGetImages()
    {
        $cartId = 42;
        $itemId = 74;
        $itemData = ['product_image' => 'Magento.png', 'random' => '3.1415926535'];
        $itemMock = $this->createMock(\Magento\Quote\Model\Quote\Item::class);
        $itemMock->expects($this->once())->method('getItemId')->willReturn($itemId);

        $expectedResult = [$itemId => $itemData['product_image']];

        $this->itemRepositoryMock->expects($this->once())->method('getList')->with($cartId)->willReturn([$itemMock]);
        $this->itemPoolMock->expects($this->once())->method('getItemData')->with($itemMock)->willReturn($itemData);

        $this->assertEquals($expectedResult, $this->model->getImages($cartId));
    }
}
