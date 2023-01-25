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

    /**
     * @var MockObject|\Magento\Catalog\Helper\Image
     */
    private $imageHelper;

    /**
     * @var MockObject|\Magento\Catalog\Model\Product\Configuration\Item\ItemResolverInterface
     */
    private $itemResolver;

    protected function setUp(): void
    {
        $this->itemRepositoryMock = $this->getMockForAbstractClass(CartItemRepositoryInterface::class);
        $this->itemPoolMock = $this->getMockForAbstractClass(ItemPoolInterface::class);
        $this->customerItem = $this->getMockBuilder(DefaultItem::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->imageHelper = $this->getMockBuilder(\Magento\Catalog\Helper\Image::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->itemResolver = $this->getMockForAbstractClass(
            \Magento\Catalog\Model\Product\Configuration\Item\ItemResolverInterface::class
        );
        $this->model = new ImageProvider(
            $this->itemRepositoryMock,
            $this->itemPoolMock,
            $this->customerItem,
            $this->imageHelper,
            $this->itemResolver
        );
    }

    public function testGetImages()
    {
        $cartId = 42;
        $itemId = 74;
        $itemData = [
            'src' => 'Url',
            'alt' => 'Label',
            'width' => 'Width',
            'height' => 'Height'
        ];
        $itemMock = $this->createMock(Item::class);
        $itemMock->expects($this->once())->method('getItemId')->willReturn($itemId);

        $expectedResult = [$itemId => $itemData];

        $this->itemRepositoryMock->expects($this->once())->method('getList')->with($cartId)->willReturn([$itemMock]);
        $this->imageHelper->expects($this->once())->method('init')->willReturnSelf();
        $this->imageHelper->expects($this->once())->method('getUrl')->willReturn('Url');
        $this->imageHelper->expects($this->once())->method('getLabel')->willReturn('Label');
        $this->imageHelper->expects($this->once())->method('getWidth')->willReturn('Width');
        $this->imageHelper->expects($this->once())->method('getHeight')->willReturn('Height');

        $this->assertEquals($expectedResult, $this->model->getImages($cartId));
    }
}
