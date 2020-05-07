<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Test\Unit\Model\Cart;

use Magento\Checkout\CustomerData\DefaultItem;
use Magento\Checkout\CustomerData\ItemPoolInterface;
use Magento\Checkout\Model\Cart\ImageProvider;
use Magento\Quote\Api\CartItemRepositoryInterface;
use Magento\Quote\Model\Quote\Item;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ImageProviderTest extends TestCase
{
    /**
     * @var ImageProvider
     */
    private $model;

    /**
     * @var MockObject|CartItemRepositoryInterface
     */
    private $itemRepositoryMock;

    /**
     * @var MockObject|ItemPoolInterface
     */
    private $itemPoolMock;

    /**
     * @var MockObject|DefaultItem
     */
    private $customerItem;

    protected function setUp(): void
    {
        $this->itemRepositoryMock = $this->getMockForAbstractClass(CartItemRepositoryInterface::class);
        $this->itemPoolMock = $this->getMockForAbstractClass(ItemPoolInterface::class);
        $this->customerItem = $this->getMockBuilder(DefaultItem::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = new ImageProvider(
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
        $itemMock = $this->createMock(Item::class);
        $itemMock->expects($this->once())->method('getItemId')->willReturn($itemId);

        $expectedResult = [$itemId => $itemData['product_image']];

        $this->itemRepositoryMock->expects($this->once())->method('getList')->with($cartId)->willReturn([$itemMock]);
        $this->customerItem->expects($this->once())->method('getItemData')->with($itemMock)->willReturn($itemData);

        $this->assertEquals($expectedResult, $this->model->getImages($cartId));
    }
}
