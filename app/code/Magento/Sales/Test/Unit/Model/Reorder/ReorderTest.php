<?php
/************************************************************************
 *
 * Copyright 2024 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Adobe.
 * ************************************************************************
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Reorder;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Cart\CustomerCartResolver;
use Magento\Quote\Model\GuestCart\GuestCartResolver;
use Magento\Quote\Model\Quote;
use Magento\Sales\Helper\Reorder as ReorderHelper;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Item;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\Reorder\OrderInfoBuyRequestGetter;
use Magento\Sales\Model\ResourceModel\Order\Item\Collection as ItemCollection;
use Magento\Sales\Model\ResourceModel\Order\Status\History\CollectionFactory;
use Magento\Sales\Model\Reorder\Reorder;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Test case for reorder test
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class ReorderTest extends TestCase
{
    /**
     * @var OrderFactory|MockObject
     */
    private $orderFactory;

    /**
     * @var Order|MockObject
     */
    private $order;

    /**
     * @var CartRepositoryInterface|MockObject
     */
    private $cartRepository;

    /**
     * @var Quote|MockObject
     */
    private $cart;

    /**
     * @var ReorderHelper|MockObject
     */
    private $reorderHelper;

    /**
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;

    /**
     * @var CustomerCartResolver|MockObject
     */
    private $customerCartProvider;

    /**
     * @var GuestCartResolver|MockObject
     */
    private $guestCartResolver;

    /**
     * @var ProductCollectionFactory|MockObject
     */
    private $productCollectionFactory;

    /**
     * @var OrderInfoBuyRequestGetter|MockObject
     */
    private $orderInfoBuyRequestGetter;

    /**
     * @var CustomerSession|MockObject
     */
    private $customerSession;

    /**
     * @var ItemCollection|MockObject
     */
    private $itemCollection;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManager;

    /**
     * @var StoreInterface|MockObject
     */
    private $store;

    /**
     * @var Reorder
     */
    private $reorder;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->orderFactory = $this->getMockBuilder(OrderFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMockForAbstractClass();
        $this->order = $this->createPartialMock(
            Order::class,
            [
                'loadByIncrementIdAndStoreId',
                'getId',
                'getCustomerId',
                'getItemsCollection',
                'getStore'
            ]
        );
        $this->cartRepository = $this->getMockBuilder(CartRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->cart = $this->createPartialMock(
            Quote::class,
            []
        );
        $this->reorderHelper = $this->createPartialMock(
            ReorderHelper::class,
            ['isAllowed']
        );
        $this->customerCartProvider = $this->createPartialMock(
            CustomerCartResolver::class,
            ['resolve']
        );
        $this->guestCartResolver = $this->createPartialMock(
            GuestCartResolver::class,
            ['resolve']
        );
        $this->productCollectionFactory = $this->getMockBuilder(ProductCollectionFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMockForAbstractClass();
        $this->itemCollection = $this->getMockBuilder(ItemCollection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderInfoBuyRequestGetter = $this->createPartialMock(
            OrderInfoBuyRequestGetter::class,
            ['getInfoBuyRequest']
        );
        $this->customerSession = $this->createPartialMock(
            CustomerSession::class,
            ['isLoggedIn']
        );
        $this->storeManager = $this->getMockBuilder(StoreManagerInterface::class)
            ->onlyMethods(['getStore'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->store = $this->getMockBuilder(StoreInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->reorder = new Reorder(
            $this->orderFactory,
            $this->customerCartProvider,
            $this->guestCartResolver,
            $this->cartRepository,
            $this->reorderHelper,
            $this->loggerMock,
            $this->productCollectionFactory,
            $this->orderInfoBuyRequestGetter,
            $this->storeManager,
            false,
            $this->customerSession
        );
    }

    /**
     * Test case for execute reorder
     *
     * @param string $orderNumber
     * @param int $orderId
     * @param string $storeId
     * @param int $customerId
     * @param bool $customerIsLoggedIn
     * @param bool $isReorderAllowed
     * @return void
     * @throws AlreadyExistsException
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @dataProvider dataProvider
     */
    public function testExecuteReorder(
        string $orderNumber,
        int $orderId,
        string $storeId,
        int $customerId,
        bool $customerIsLoggedIn,
        bool $isReorderAllowed,
    ): void {
        $item1 = $this->createPartialMock(
            Item::class,
            ['getParentItem', 'getProductId', 'getId']
        );
        $item1->expects($this->any())
            ->method('getParentItem')
            ->willReturn(null);
        $item1->expects($this->any())
            ->method('getProductId')
            ->willReturn(1);
        $item1->expects($this->any())
            ->method('getId')
            ->willReturn(5);
        $item2 = $this->createPartialMock(
            Item::class,
            ['getParentItem', 'getProductId', 'getId']
        );
        $item2->expects($this->any())
            ->method('getParentItem')
            ->willReturn(null);
        $item2->expects($this->any())
            ->method('getProductId')
            ->willReturn(2);
        $item2->expects($this->any())
            ->method('getId')
            ->willReturn(5);
        $collection = $this->createMock(ItemCollection::class);
        $collection->expects($this->any())->method('getIterator')
            ->willReturn(new \ArrayIterator([$item1, $item2]));
        $this->order->expects($this->once())
            ->method('getItemsCollection')
            ->willReturn($collection);
         $productCollection = $this->getMockBuilder(Collection::class)
            ->onlyMethods(
                [
                    'getItems',
                    'addIdFilter',
                    'addStoreFilter',
                    'addAttributeToSelect',
                    'joinAttribute',
                    'addOptionsToResult',
                    'getIterator',
                    'setStore'
                ]
            )
             ->addMethods(['getStore'])
            ->disableOriginalConstructor()
            ->getMock();
        $productCollection->expects($this->any())->method('setStore')->willReturnSelf();
        $productCollection->expects($this->any())->method('addIdFilter')->willReturnSelf();
        $productCollection->expects($this->any())->method('addStoreFilter')->willReturnSelf();
        $productCollection->expects($this->once())->method('addAttributeToSelect')->willReturnSelf();
        $productCollection->expects($this->any())->method('joinAttribute')->willReturnSelf();
        $productCollection->expects($this->once())->method('addOptionsToResult')->willReturnSelf();
        $this->productCollectionFactory->expects($this->once())
            ->method('create')
            ->willReturn($productCollection);
//        $product1 = $this->getMockForAbstractClass(ProductInterface::class);
//        $product2 = $this->getMockForAbstractClass(ProductInterface::class);
//        $productCollection->expects($this->once())->method('getItems')->willReturn([$product1, $product2]);
        $productCollection->expects($this->once())->method('getItems')->willReturn([]);
        $this->orderFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->order);
        $this->order->expects($this->once())
            ->method('loadByIncrementIdAndStoreId')
            ->with($orderNumber, $storeId)
            ->willReturnSelf();
        $this->order->expects($this->once())
            ->method('getId')
            ->willReturn($orderId);
        $this->order->expects($this->once())
            ->method('getCustomerId')
            ->willReturn($customerId);
        $this->order->expects($this->once())
            ->method('getStore')
            ->willReturn($this->store);
        $this->customerSession->expects($this->once())
            ->method('isLoggedIn')
            ->willReturn($customerIsLoggedIn);
        $this->guestCartResolver->expects($this->any())
            ->method('resolve')
            ->willReturn($this->cart);
        $this->customerCartProvider->expects($this->any())
            ->method('resolve')
            ->with($customerId)
            ->willReturn($this->cart);
        $this->reorderHelper->expects($this->any())
            ->method('isAllowed')
            ->with($this->store)
            ->willReturn($isReorderAllowed);
        $this->storeManager->expects($this->once())
            ->method('getStore')
            ->willReturn($this->store);
        $this->store->expects($this->once())
            ->method('getId')
            ->willReturn($storeId);
        $this->cartRepository->expects($this->once())
            ->method('save')
            ->with($this->cart)
            ->willReturnSelf();
//        $infoBuyRequest = new DataObject(['options' => [
//            [
//                'option_id' => 1,
//                'option_value' => 2
//            ]
//        ]]);
//        $this->orderInfoBuyRequestGetter->expects($this->once())
//            ->method('getInfoBuyRequest')
//            ->willReturn($infoBuyRequest);
        $savedCart = $this->getMockBuilder(\Magento\Quote\Api\Data\CartInterface::class)
            ->disableOriginalConstructor()
            ->addMethods(['setHasError'])
            ->getMockForAbstractClass();
        $this->cartRepository->expects($this->once())
            ->method('get')
            ->willReturn($savedCart);

        $output = $this->reorder->execute($orderNumber, $storeId);
        $this->assertNotEmpty($output);
    }

    /**
     * @return array
     */
    public function dataProvider()
    {
        return [
            'test case 1' => ['000001', 1, '1', 1, true, true],
        ];
    }
}
