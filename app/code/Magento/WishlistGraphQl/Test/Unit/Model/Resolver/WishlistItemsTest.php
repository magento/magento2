<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\WishlistGraphQl\Test\Unit\Model\Resolver;

use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\GraphQl\Model\Query\ContextExtensionInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Wishlist\Model\ResourceModel\Item;
use Magento\Wishlist\Model\ResourceModel\Item\Collection as WishlistItemCollection;
use Magento\Wishlist\Model\ResourceModel\Item\CollectionFactory as WishlistItemCollectionFactory;
use Magento\Wishlist\Model\Wishlist;
use Magento\WishlistGraphQl\Model\Resolver\WishlistItems;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class WishlistItemsTest extends TestCase
{
    /**
     * @var WishlistItemCollectionFactory|MockObject
     */
    private WishlistItemCollectionFactory $wishlistItemCollectionFactory;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private StoreManagerInterface $storeManager;

    /**
     * @return void
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    protected function setUp(): void
    {
        $this->wishlistItemCollectionFactory = $this->createMock(WishlistItemCollectionFactory::class);
        $this->storeManager = $this->createMock(StoreManagerInterface::class);
    }

    /**
     * @return void
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testResolve(): void
    {
        $storeId = $itemId = 1;

        $field = $this->createMock(Field::class);
        $context = $this->getMockBuilder(ContextInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $store = $this->createMock(StoreInterface::class);
        $store->expects($this->once())->method('getId')->willReturn($storeId);

        $extensionAttributes = $this->getMockBuilder(ContextExtensionInterface::class)
            ->disableOriginalConstructor()
            ->addMethods(['getStore'])
            ->getMock();
        $extensionAttributes->expects($this->exactly(2))
            ->method('getStore')
            ->willReturn($store);
        $context->expects($this->exactly(2))
            ->method('getExtensionAttributes')
            ->willReturn($extensionAttributes);
        $info = $this->createMock(ResolveInfo::class);
        $wishlist = $this->createMock(Wishlist::class);

        $item = $this->getMockBuilder(Item::class)
            ->addMethods(['getId', 'getData', 'getDescription', 'getAddedAt', 'getProduct'])
            ->disableOriginalConstructor()
            ->getMock();
        $item->expects($this->once())->method('getId')->willReturn($itemId);
        $item->expects($this->once())->method('getData')->with('qty');
        $item->expects($this->once())->method('getDescription');
        $item->expects($this->once())->method('getAddedAt');
        $item->expects($this->once())->method('getProduct');

        $wishlistCollection = $this->createMock(WishlistItemCollection::class);
        $wishlistCollection->expects($this->once())
            ->method('addWishlistFilter')
            ->willReturnSelf();
        $wishlistCollection->expects($this->once())
            ->method('addStoreFilter')
            ->with($storeId)
            ->willReturnSelf();
        $wishlistCollection->expects($this->once())->method('setVisibilityFilter')->willReturnSelf();
        $wishlistCollection->expects($this->once())->method('setCurPage')->willReturnSelf();
        $wishlistCollection->expects($this->once())->method('setPageSize')->willReturnSelf();
        $wishlistCollection->expects($this->once())->method('getItems')->willReturn([$item]);
        $wishlistCollection->expects($this->once())->method('getCurPage');
        $wishlistCollection->expects($this->once())->method('getPageSize');
        $wishlistCollection->expects($this->once())->method('getLastPageNumber');
        $this->wishlistItemCollectionFactory->expects($this->once())
            ->method('create')
            ->willReturn($wishlistCollection);

        $resolver = new WishlistItems($this->wishlistItemCollectionFactory, $this->storeManager);
        $resolver->resolve($field, $context, $info, ['model' => $wishlist]);
    }
}
