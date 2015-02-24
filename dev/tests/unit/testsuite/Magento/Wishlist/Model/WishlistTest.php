<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Model;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class WishlistTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $registry;

    /**
     * @var \Magento\Catalog\Helper\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productHelper;

    /**
     * @var \Magento\Wishlist\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $helper;

    /**
     * @var \Magento\Wishlist\Model\Resource\Wishlist|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resource;

    /**
     * @var \Magento\Wishlist\Model\Resource\Wishlist\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $collection;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $date;

    /**
     * @var \Magento\Wishlist\Model\ItemFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $itemFactory;

    /**
     * @var \Magento\Wishlist\Model\Resource\Item\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $itemsFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productFactory;

    /**
     * @var \Magento\Framework\Math\Random|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $mathRandom;

    /**
     * @var \Magento\Framework\Stdlib\DateTime|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dateTime;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventDispatcher;

    /**
     * @var Wishlist
     */
    protected $wishlist;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productRepository;

    public function setUp()
    {
        $context = $this->getMockBuilder('Magento\Framework\Model\Context')
            ->disableOriginalConstructor()
            ->getMock();

        $this->eventDispatcher = $this->getMockBuilder('Magento\Framework\Event\ManagerInterface')
            ->getMock();
        $this->registry = $this->getMockBuilder('Magento\Framework\Registry')
            ->disableOriginalConstructor()
            ->getMock();
        $this->productHelper = $this->getMockBuilder('Magento\Catalog\Helper\Product')
            ->disableOriginalConstructor()
            ->getMock();
        $this->helper = $this->getMockBuilder('Magento\Wishlist\Helper\Data')
            ->disableOriginalConstructor()
            ->getMock();
        $this->resource = $this->getMockBuilder('Magento\Wishlist\Model\Resource\Wishlist')
            ->disableOriginalConstructor()
            ->getMock();
        $this->collection = $this->getMockBuilder('Magento\Wishlist\Model\Resource\Wishlist\Collection')
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManager = $this->getMockBuilder('Magento\Store\Model\StoreManagerInterface')
            ->getMock();
        $this->date = $this->getMockBuilder('Magento\Framework\Stdlib\DateTime\DateTime')
            ->disableOriginalConstructor()
            ->getMock();
        $this->itemFactory = $this->getMockBuilder('Magento\Wishlist\Model\ItemFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->itemsFactory = $this->getMockBuilder('Magento\Wishlist\Model\Resource\Item\CollectionFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->productFactory = $this->getMockBuilder('Magento\Catalog\Model\ProductFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->mathRandom = $this->getMockBuilder('Magento\Framework\Math\Random')
            ->disableOriginalConstructor()
            ->getMock();
        $this->dateTime = $this->getMockBuilder('Magento\Framework\Stdlib\DateTime')
            ->disableOriginalConstructor()
            ->getMock();
        $this->productRepository = $this->getMock('Magento\Catalog\Api\ProductRepositoryInterface');

        $context->expects($this->once())
            ->method('getEventDispatcher')
            ->will($this->returnValue($this->eventDispatcher));

        $this->wishlist = new Wishlist(
            $context,
            $this->registry,
            $this->productHelper,
            $this->helper,
            $this->resource,
            $this->collection,
            $this->storeManager,
            $this->date,
            $this->itemFactory,
            $this->itemsFactory,
            $this->productFactory,
            $this->mathRandom,
            $this->dateTime,
            $this->productRepository,
            false
        );
    }

    public function testLoadByCustomerId()
    {
        $customerId = 1;
        $customerIdFieldName = 'customer_id';
        $sharingCode = 'expected_sharing_code';
        $this->eventDispatcher->expects($this->any())
            ->method('dispatch');
        $this->resource->expects($this->any())
            ->method('getCustomerIdFieldName');
        $this->resource->expects($this->once())
            ->method('load')
            ->with($this->logicalOr($this->wishlist, $customerId, $customerIdFieldName));
        $this->mathRandom->expects($this->once())
            ->method('getUniqueHash')
            ->will($this->returnValue($sharingCode));

        $this->assertInstanceOf(
            'Magento\Wishlist\Model\Wishlist',
            $this->wishlist->loadByCustomerId($customerId, true)
        );
        $this->assertEquals($customerId, $this->wishlist->getCustomerId());
        $this->assertEquals($sharingCode, $this->wishlist->getSharingCode());
    }

    /**
     * @param int|\Magento\Wishlist\Model\Item|\PHPUnit_Framework_MockObject_MockObject $itemId
     * @param \Magento\Framework\Object $buyRequest
     * @param null|array|\Magento\Framework\Object $param
     * @throws \Magento\Framework\Model\Exception
     *
     * @dataProvider updateItemDataProvider
     */
    public function testUpdateItem($itemId, $buyRequest, $param)
    {
        $storeId = 1;
        $productId = 1;
        $stores = [(new \Magento\Framework\Object())->setId($storeId)];

        $newItem = $this->getMockBuilder('Magento\Wishlist\Model\Item')->disableOriginalConstructor()->getMock();
        $newItem->expects($this->any())->method('setProductId')->will($this->returnSelf());
        $newItem->expects($this->any())->method('setWishlistId')->will($this->returnSelf());
        $newItem->expects($this->any())->method('setStoreId')->will($this->returnSelf());
        $newItem->expects($this->any())->method('setOptions')->will($this->returnSelf());
        $newItem->expects($this->any())->method('setProduct')->will($this->returnSelf());
        $newItem->expects($this->any())->method('setQty')->will($this->returnSelf());
        $newItem->expects($this->any())->method('getItem')->will($this->returnValue(2));

        $this->itemFactory->expects($this->once())->method('create')->will($this->returnValue($newItem));

        $this->storeManager->expects($this->any())->method('getStores')->will($this->returnValue($stores));
        $this->storeManager->expects($this->any())->method('getStore')->will($this->returnValue($stores[0]));

        $product = $this->getMockBuilder('Magento\Catalog\Model\Product')->disableOriginalConstructor()->getMock();
        $product->expects($this->any())->method('getId')->will($this->returnValue($productId));
        $product->expects($this->any())->method('getStoreId')->will($this->returnValue($storeId));

        $instanceType = $this->getMockBuilder('Magento\Catalog\Model\Product\Type\AbstractType')
            ->disableOriginalConstructor()
            ->getMock();
        $instanceType->expects($this->once())
            ->method('processConfiguration')
            ->will(
                $this->returnValue(
                    $this->getMockBuilder('Magento\Catalog\Model\Product')->disableOriginalConstructor()->getMock()
                )
            );

        $newProduct = $this->getMockBuilder('Magento\Catalog\Model\Product')->disableOriginalConstructor()->getMock();
        $newProduct->expects($this->any())
            ->method('setStoreId')
            ->with($storeId)
            ->will($this->returnSelf());
        $newProduct->expects($this->once())
            ->method('getTypeInstance')
            ->will($this->returnValue($instanceType));

        $item = $this->getMockBuilder('Magento\Wishlist\Model\Item')->disableOriginalConstructor()->getMock();
        $item->expects($this->once())
            ->method('getProduct')
            ->will($this->returnValue($product));

        $items = $this->getMockBuilder('Magento\Wishlist\Model\Resource\Item\Collection')
            ->disableOriginalConstructor()
            ->getMock();

        $items->expects($this->once())
            ->method('addWishlistFilter')
            ->will($this->returnSelf());
        $items->expects($this->once())
            ->method('addStoreFilter')
            ->will($this->returnSelf());
        $items->expects($this->once())
            ->method('setVisibilityFilter')
            ->will($this->returnSelf());
        $items->expects($this->once())
            ->method('getItemById')
            ->will($this->returnValue($item));
        $items->expects($this->any())
            ->method('getIterator')
            ->will($this->returnValue(new \ArrayIterator([$item])));

        $this->itemsFactory->expects($this->any())
            ->method('create')
            ->will($this->returnValue($items));

        $this->productRepository->expects($this->once())
            ->method('getById')
            ->with($productId, false, $storeId)
            ->will($this->returnValue($newProduct));

        $this->assertInstanceOf(
            'Magento\Wishlist\Model\Wishlist',
            $this->wishlist->updateItem($itemId, $buyRequest, $param)
        );
    }

    /**
     * @return array
     */
    public function updateItemDataProvider()
    {
        return [
            '0' => [1, new \Magento\Framework\Object(), null],
        ];
    }
}
