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
    private $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Quote\Api\CartItemRepositoryInterface
     */
    private $itemRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Checkout\CustomerData\ItemPoolInterface
     */
    private $itemPoolMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Checkout\CustomerData\DefaultItem
     */
    private $customerItem;

    protected function setUp()
    {
        $this->itemRepositoryMock = $this->createMock(\Magento\Quote\Api\CartItemRepositoryInterface::class);
        $this->itemPoolMock = $this->createMock(\Magento\Checkout\CustomerData\ItemPoolInterface::class);
        $this->customerItem = $this->getMockBuilder(\Magento\Checkout\CustomerData\DefaultItem::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = new \Magento\Checkout\Model\Cart\ImageProvider(
            $this->itemRepositoryMock,
            $this->itemPoolMock,
            $this->customerItem
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
        $this->customerItem->expects($this->once())->method('getItemData')->with($itemMock)->willReturn($itemData);

        $this->assertEquals($expectedResult, $this->model->getImages($cartId));
    }
}
