<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Test\Unit\Model;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\State;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\CollectionFactory;
use Magento\Framework\Session\SessionStartChecker;
use Magento\Framework\Session\Storage;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderFactory;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Test class for \Magento\Checkout\Model\Session
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SessionTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $helper;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $session;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->helper = new ObjectManager($this);
        $objects = [
            [
                SessionStartChecker::class,
                $this->createMock(SessionStartChecker::class)
            ]
        ];
        $this->helper->prepareObjectManager($objects);
    }

    /**
     * @param int|null $orderId
     * @param int|null $incrementId
     * @param \Closure $orderMock
     *
     * @return void
     * @dataProvider getLastRealOrderDataProvider
     */
    public function testGetLastRealOrder($orderId, $incrementId, \Closure $orderMock): void
    {
        $orderMock = $orderMock($this);
        $orderFactory = $this->getMockBuilder(OrderFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $orderFactory->expects($this->once())->method('create')->willReturn($orderMock);

        $messageCollectionFactory = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $quoteRepository = $this->getMockForAbstractClass(CartRepositoryInterface::class);

        $appState = $this->getMockBuilder(State::class)
            ->addMethods(['isInstalled'])
            ->disableOriginalConstructor()
            ->getMock();
        $appState->expects($this->any())->method('isInstalled')->willReturn(true);

        $request = $this->createMock(Http::class);
        $request->expects($this->any())->method('getHttpHost')->willReturn([]);

        $constructArguments = $this->helper->getConstructArguments(
            Session::class,
            [
                'request' => $request,
                'orderFactory' => $orderFactory,
                'messageCollectionFactory' => $messageCollectionFactory,
                'quoteRepository' => $quoteRepository,
                'storage' => new Storage()
            ]
        );
        $this->session = $this->helper->getObject(Session::class, $constructArguments);
        $this->session->setLastRealOrderId($orderId);
        $this->assertSame($orderMock, $this->session->getLastRealOrder());

        if ($orderId == $incrementId) {
            $this->assertSame($orderMock, $this->session->getLastRealOrder());
        }
    }

    /**
     * @return array
     */
    public static function getLastRealOrderDataProvider(): array
    {
        return [
            [null, 1, static fn (self $testCase) => $testCase->_getOrderMock(1, null)],
            [1, 1, static fn (self $testCase) => $testCase->_getOrderMock(1, 1)],
            [1, null, static fn (self $testCase) => $testCase->_getOrderMock(null, 1)]
        ];
    }

    /**
     * @param int|null $incrementId
     * @param int|null $orderId
     *
     * @return Order|MockObject
     */
    protected function _getOrderMock($incrementId, $orderId): MockObject
    {
        /** @var MockObject|\Magento\Sales\Model\Order $order */
        $order = $this->getMockBuilder(Order::class)->disableOriginalConstructor()
            ->onlyMethods(['getIncrementId', 'loadByIncrementId', '__sleep'])->getMock();

        if ($orderId && $incrementId) {
            $order->expects($this->once())->method('getIncrementId')->willReturn($incrementId);
            $order->expects($this->once())->method('loadByIncrementId')->with($orderId);
        }

        return $order;
    }

    /**
     * @param string $paramToClear
     *
     * @return void
     * @dataProvider clearHelperDataDataProvider
     */
    public function testClearHelperData(string $paramToClear): void
    {
        $storage = new Storage('default', [$paramToClear => 'test_data']);
        $this->session = $this->helper->getObject(Session::class, ['storage' => $storage]);

        $this->session->clearHelperData();
        $this->assertNull($this->session->getData($paramToClear));
    }

    /**
     * @return array
     */
    public static function clearHelperDataDataProvider(): array
    {
        return [
            ['redirect_url'],
            ['last_order_id'],
            ['last_real_order_id'],
            ['additional_messages']
        ];
    }

    /**
     * @param bool $hasOrderId
     * @param bool $hasQuoteId
     *
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @dataProvider restoreQuoteDataProvider
     */
    public function testRestoreQuote(bool $hasOrderId, bool $hasQuoteId): void
    {
        $order = $this->createPartialMock(
            Order::class,
            ['getId', 'loadByIncrementId']
        );
        $order->expects($this->once())->method('getId')->willReturn($hasOrderId ? 'order id' : null);
        $orderFactory = $this->createPartialMock(OrderFactory::class, ['create']);
        $orderFactory->expects($this->once())->method('create')->willReturn($order);
        $quoteRepository = $this->getMockBuilder(CartRepositoryInterface::class)
            ->onlyMethods(['save'])
            ->getMockForAbstractClass();
        $storage = new Storage();
        $store = $this->createMock(Store::class);
        $storeManager = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $storeManager->expects($this->any())->method('getStore')->willReturn($store);
        $eventManager = $this->getMockForAbstractClass(ManagerInterface::class);

        /** @var Session $session */
        $session = $this->helper->getObject(
            Session::class,
            [
                'orderFactory' => $orderFactory,
                'quoteRepository' => $quoteRepository,
                'storage' => $storage,
                'storeManager' => $storeManager,
                'eventManager' => $eventManager
            ]
        );
        $lastOrderId = 'last order id';
        $quoteId = 'quote id';
        $anotherQuoteId = 'another quote id';
        $session->setLastRealOrderId($lastOrderId);
        $session->setQuoteId($quoteId);

        if ($hasOrderId) {
            $order->setQuoteId($quoteId);
            $quote = $this->createPartialMock(
                Quote::class,
                ['setIsActive', 'getId', 'setReservedOrderId', 'save']
            );
            if ($hasQuoteId) {
                $quoteRepository->expects($this->once())->method('get')->with($quoteId)->willReturn($quote);
                $quote->expects(
                    $this->any()
                )->method(
                    'getId'
                )->willReturn(
                    $anotherQuoteId
                );
                $eventManager->expects(
                    $this->once()
                )->method(
                    'dispatch'
                )->with(
                    'restore_quote',
                    ['order' => $order, 'quote' => $quote]
                );
                $quote->expects(
                    $this->once()
                )->method(
                    'setIsActive'
                )->with(
                    1
                )->willReturnSelf();
                $quote->expects(
                    $this->once()
                )->method(
                    'setReservedOrderId'
                )->with(
                    $this->isNull()
                )->willReturnSelf();
                $quoteRepository->expects($this->once())->method('save')->with($quote);
            } else {
                $quoteRepository->expects($this->once())
                    ->method('get')
                    ->with($quoteId)
                    ->willThrowException(
                        new NoSuchEntityException()
                    );
                $quote->expects($this->never())->method('setIsActive');
                $quote->expects($this->never())->method('setReservedOrderId');
                $quote->expects($this->never())->method('save');
            }
        }
        $result = $session->restoreQuote();
        if ($hasOrderId && $hasQuoteId) {
            $this->assertNull($session->getLastRealOrderId());
            $this->assertEquals($anotherQuoteId, $session->getQuoteId());
        } else {
            $this->assertEquals($lastOrderId, $session->getLastRealOrderId());
            $this->assertEquals($quoteId, $session->getQuoteId());
        }
        $this->assertEquals($result, $hasOrderId && $hasQuoteId);
    }

    /**
     * @return array
     */
    public static function restoreQuoteDataProvider(): array
    {
        return [[true, true], [true, false], [false, true], [false, false]];
    }

    /**
     * @return void
     */
    public function testHasQuote(): void
    {
        $quote = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->getMock();
        $session = $this->helper->getObject(Session::class, ['quote' => $quote]);
        $this->assertFalse($session->hasQuote());
    }

    /**
     * @return void
     */
    public function testReplaceQuote(): void
    {
        $replaceQuoteId = 3;
        $websiteId = 1;

        $store = $this->getMockBuilder(Store::class)->disableOriginalConstructor()
            ->onlyMethods(['getWebsiteId'])
            ->getMock();
        $store->expects($this->any())
            ->method('getWebsiteId')
            ->willReturn($websiteId);

        $storeManager = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $storeManager->expects($this->any())
            ->method('getStore')
            ->willReturn($store);

        $quote = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->getMock();
        $quote->expects($this->once())
            ->method('getId')
            ->willReturn($replaceQuoteId);

        $storage = $this->getMockBuilder(Storage::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['setData', 'getData'])
            ->getMock();

        $storage->expects($this->any())
            ->method('getData')
            ->willReturn($replaceQuoteId);
        $storage->expects($this->any())
            ->method('setData');

        $quoteIdMaskMock = $this->getMockBuilder(QuoteIdMask::class)
            ->addMethods(['getMaskedId', 'setQuoteId'])
            ->onlyMethods(['load', 'save'])
            ->disableOriginalConstructor()
            ->getMock();
        $quoteIdMaskMock->expects($this->once())->method('load')->with($replaceQuoteId, 'quote_id')->willReturnSelf();
        $quoteIdMaskMock->expects($this->once())->method('getMaskedId')->willReturn(null);
        $quoteIdMaskMock->expects($this->once())->method('setQuoteId')->with($replaceQuoteId)->willReturnSelf();
        $quoteIdMaskMock->expects($this->once())->method('save');

        $quoteIdMaskFactoryMock = $this->createPartialMock(QuoteIdMaskFactory::class, ['create']);
        $quoteIdMaskFactoryMock->expects($this->once())->method('create')->willReturn($quoteIdMaskMock);

        $session = $this->helper->getObject(
            Session::class,
            [
                'storeManager' => $storeManager,
                'storage' => $storage,
                'quoteIdMaskFactory' => $quoteIdMaskFactoryMock
            ]
        );

        $session->replaceQuote($quote);

        $this->assertSame($quote, $session->getQuote());
        $this->assertEquals($replaceQuoteId, $session->getQuoteId());
    }

    /**
     * @return void
     */
    public function testClearStorage(): void
    {
        $storage = $this->getMockBuilder(Storage::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['unsetData'])
            ->getMock();
        $storage->expects($this->once())
            ->method('unsetData');

        $session = $this->helper->getObject(
            Session::class,
            [
                'storage' => $storage
            ]
        );

        $this->assertInstanceOf(Session::class, $session->clearStorage());
        $this->assertFalse($session->hasQuote());
    }

    /**
     * @return void
     */
    public function testResetCheckout(): void
    {
        /** @var $session \Magento\Checkout\Model\Session */
        $session = $this->helper->getObject(
            Session::class,
            ['storage' => new Storage()]
        );
        $session->resetCheckout();
        $this->assertEquals(Session::CHECKOUT_STATE_BEGIN, $session->getCheckoutState());
    }

    /**
     * @return void
     */
    public function testGetStepData(): void
    {
        $stepData = [
            'simple' => 'data',
            'complex' => [
                'key' => 'value'
            ]
        ];
        /** @var $session \Magento\Checkout\Model\Session */
        $session = $this->helper->getObject(
            Session::class,
            ['storage' => new Storage()]
        );
        $session->setSteps($stepData);
        $this->assertEquals($stepData, $session->getStepData());
        $this->assertFalse($session->getStepData('invalid_key'));
        $this->assertEquals($stepData['complex'], $session->getStepData('complex'));
        $this->assertFalse($session->getStepData('simple', 'invalid_sub_key'));
        $this->assertEquals($stepData['complex']['key'], $session->getStepData('complex', 'key'));
    }

    /**
     * Ensure that if quote not exist for customer quote will be null.
     *
     * @return void
     */
    public function testGetQuote(): void
    {
        $storeManager = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $customerSession = $this->createMock(\Magento\Customer\Model\Session::class);
        $quoteRepository = $this->getMockForAbstractClass(CartRepositoryInterface::class);
        $quoteFactory = $this->createMock(QuoteFactory::class);
        $quote = $this->createMock(Quote::class);
        $logger = $this->getMockForAbstractClass(LoggerInterface::class);
        $loggerMethods = get_class_methods(LoggerInterface::class);

        $quoteFactory->expects($this->once())
            ->method('create')
            ->willReturn($quote);
        $customerSession->expects($this->exactly(3))
            ->method('isLoggedIn')
            ->willReturn(true);
        $store = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getWebsiteId'])
            ->getMock();
        $storeManager->expects($this->any())
            ->method('getStore')
            ->willReturn($store);
        $storage = $this->getMockBuilder(Storage::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['setData', 'getData'])
            ->getMock();
        $storage
            ->method('getData')
            ->willReturnOnConsecutiveCalls(null, null, 1);
        $quoteRepository->expects($this->once())
            ->method('getActiveForCustomer')
            ->willThrowException(new NoSuchEntityException());

        foreach ($loggerMethods as $method) {
            $logger->expects($this->never())->method($method);
        }

        $quote->expects($this->once())
            ->method('setCustomer')
            ->with(null);

        $constructArguments = $this->helper->getConstructArguments(
            Session::class,
            [
                'storeManager' => $storeManager,
                'quoteRepository' => $quoteRepository,
                'customerSession' => $customerSession,
                'storage' => $storage,
                'quoteFactory' => $quoteFactory,
                'logger' => $logger
            ]
        );
        $this->session = $this->helper->getObject(Session::class, $constructArguments);
        $this->session->getQuote();
    }

    /**
     * @return void
     */
    public function testSetStepData(): void
    {
        $stepData = [
            'complex' => [
                'key' => 'value',
            ],
        ];
        /** @var $session \Magento\Checkout\Model\Session */
        $session = $this->helper->getObject(
            Session::class,
            ['storage' => new Storage()]
        );
        $session->setSteps($stepData);

        $session->setStepData('complex', 'key2', 'value2');
        $session->setStepData('simple', ['key' => 'value']);
        $session->setStepData('simple', 'key2', 'value2');
        $expectedResult = [
            'complex' => [
                'key' => 'value',
                'key2' => 'value2'
            ],
            'simple' => [
                'key' => 'value',
                'key2' => 'value2'
            ]
        ];
        $this->assertEquals($expectedResult, $session->getSteps());
    }
}
