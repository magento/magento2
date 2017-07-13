<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

/**
 * Test class for \Magento\Checkout\Model\Session
 */
namespace Magento\Checkout\Test\Unit\Model;

use \Magento\Checkout\Model\Session;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SessionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $_helper;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_session;

    protected function setUp()
    {
        $this->_helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
    }

    /**
     * @param int|null $orderId
     * @param int|null $incrementId
     * @param \Magento\Sales\Model\Order|\PHPUnit_Framework_MockObject_MockObject $orderMock
     * @dataProvider getLastRealOrderDataProvider
     */
    public function testGetLastRealOrder($orderId, $incrementId, $orderMock)
    {
        $orderFactory = $this->getMockBuilder(\Magento\Sales\Model\OrderFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $orderFactory->expects($this->once())->method('create')->will($this->returnValue($orderMock));

        $messageCollectionFactory = $this->getMockBuilder(\Magento\Framework\Message\CollectionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $quoteRepository = $this->createMock(\Magento\Quote\Api\CartRepositoryInterface::class);

        $appState = $this->createPartialMock(\Magento\Framework\App\State::class, ['isInstalled']);
        $appState->expects($this->any())->method('isInstalled')->will($this->returnValue(true));

        $request = $this->createMock(\Magento\Framework\App\Request\Http::class);
        $request->expects($this->any())->method('getHttpHost')->will($this->returnValue([]));

        $constructArguments = $this->_helper->getConstructArguments(
            \Magento\Checkout\Model\Session::class,
            [
                'request' => $request,
                'orderFactory' => $orderFactory,
                'messageCollectionFactory' => $messageCollectionFactory,
                'quoteRepository' => $quoteRepository,
                'storage' => new \Magento\Framework\Session\Storage()
            ]
        );
        $this->_session = $this->_helper->getObject(\Magento\Checkout\Model\Session::class, $constructArguments);
        $this->_session->setLastRealOrderId($orderId);

        $this->assertSame($orderMock, $this->_session->getLastRealOrder());
        if ($orderId == $incrementId) {
            $this->assertSame($orderMock, $this->_session->getLastRealOrder());
        }
    }

    /**
     * @return array
     */
    public function getLastRealOrderDataProvider()
    {
        return [
            [null, 1, $this->_getOrderMock(1, null)],
            [1, 1, $this->_getOrderMock(1, 1)],
            [1, null, $this->_getOrderMock(null, 1)]
        ];
    }

    /**
     * @param int|null $incrementId
     * @param int|null $orderId
     * @return \Magento\Sales\Model\Order|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getOrderMock($incrementId, $orderId)
    {
        /** @var $order \PHPUnit_Framework_MockObject_MockObject|\Magento\Sales\Model\Order */
        $order = $this->getMockBuilder(
            \Magento\Sales\Model\Order::class
        )->disableOriginalConstructor()->setMethods(
            ['getIncrementId', 'loadByIncrementId', '__sleep', '__wakeup']
        )->getMock();

        if ($orderId && $incrementId) {
            $order->expects($this->once())->method('getIncrementId')->will($this->returnValue($incrementId));
            $order->expects($this->once())->method('loadByIncrementId')->with($orderId);
        }

        return $order;
    }

    /**
     * @param $paramToClear
     * @dataProvider clearHelperDataDataProvider
     */
    public function testClearHelperData($paramToClear)
    {
        $storage = new \Magento\Framework\Session\Storage('default', [$paramToClear => 'test_data']);
        $this->_session = $this->_helper->getObject(\Magento\Checkout\Model\Session::class, ['storage' => $storage]);

        $this->_session->clearHelperData();
        $this->assertNull($this->_session->getData($paramToClear));
    }

    /**
     * @return array
     */
    public function clearHelperDataDataProvider()
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
     * @dataProvider restoreQuoteDataProvider
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testRestoreQuote($hasOrderId, $hasQuoteId)
    {
        $order = $this->createPartialMock(\Magento\Sales\Model\Order::class, ['getId', 'loadByIncrementId', '__wakeup']);
        $order->expects($this->once())->method('getId')->will($this->returnValue($hasOrderId ? 'order id' : null));
        $orderFactory = $this->createPartialMock(\Magento\Sales\Model\OrderFactory::class, ['create']);
        $orderFactory->expects($this->once())->method('create')->will($this->returnValue($order));
        $quoteRepository = $this->getMockBuilder(\Magento\Quote\Api\CartRepositoryInterface::class)
            ->setMethods(['save'])
            ->getMockForAbstractClass();
        $storage = new \Magento\Framework\Session\Storage();
        $store = $this->createMock(\Magento\Store\Model\Store::class);
        $storeManager = $this->getMockForAbstractClass(\Magento\Store\Model\StoreManagerInterface::class);
        $storeManager->expects($this->any())->method('getStore')->will($this->returnValue($store));
        $eventManager = $this->getMockForAbstractClass(\Magento\Framework\Event\ManagerInterface::class);

        /** @var Session $session */
        $session = $this->_helper->getObject(
            \Magento\Checkout\Model\Session::class,
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
            $quote = $this->createPartialMock(\Magento\Quote\Model\Quote::class, ['setIsActive', 'getId', 'setReservedOrderId', '__wakeup', 'save']);
            if ($hasQuoteId) {
                $quoteRepository->expects($this->once())->method('get')->with($quoteId)->willReturn($quote);
                $quote->expects(
                    $this->any()
                )->method(
                    'getId'
                )->will(
                    $this->returnValue($anotherQuoteId)
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
                    $this->equalTo(1)
                )->will(
                    $this->returnSelf()
                );
                $quote->expects(
                    $this->once()
                )->method(
                    'setReservedOrderId'
                )->with(
                    $this->isNull()
                )->will(
                    $this->returnSelf()
                );
                $quoteRepository->expects($this->once())->method('save')->with($quote);
            } else {
                $quoteRepository->expects($this->once())
                    ->method('get')
                    ->with($quoteId)
                    ->willThrowException(
                        new \Magento\Framework\Exception\NoSuchEntityException()
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
    public function restoreQuoteDataProvider()
    {
        return [[true, true], [true, false], [false, true], [false, false]];
    }

    public function testHasQuote()
    {
        $quote = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)
            ->disableOriginalConstructor()
            ->getMock();
        $session = $this->_helper->getObject(\Magento\Checkout\Model\Session::class, ['quote' => $quote]);
        $this->assertFalse($session->hasQuote());
    }

    public function testReplaceQuote()
    {
        $replaceQuoteId = 3;
        $websiteId = 1;

        $store = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->disableOriginalConstructor()
            ->setMethods(['getWebsiteId', '__wakeup'])
            ->getMock();
        $store->expects($this->any())
            ->method('getWebsiteId')
            ->will($this->returnValue($websiteId));

        $storeManager = $this->getMockForAbstractClass(\Magento\Store\Model\StoreManagerInterface::class);
        $storeManager->expects($this->any())
            ->method('getStore')
            ->will($this->returnValue($store));

        $quote = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)
            ->disableOriginalConstructor()
            ->getMock();
        $quote->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($replaceQuoteId));

        $storage = $this->getMockBuilder(\Magento\Framework\Session\Storage::class)
            ->disableOriginalConstructor()
            ->setMethods(['setData', 'getData'])
            ->getMock();

        $storage->expects($this->any())
            ->method('getData')
            ->willReturn($replaceQuoteId);
        $storage->expects($this->any())
            ->method('setData');

        $quoteIdMaskMock = $this->createPartialMock(\Magento\Quote\Model\QuoteIdMask::class, ['getMaskedId', 'load', 'setQuoteId', 'save']);
        $quoteIdMaskMock->expects($this->once())->method('load')->with($replaceQuoteId, 'quote_id')->willReturnSelf();
        $quoteIdMaskMock->expects($this->once())->method('getMaskedId')->willReturn(null);
        $quoteIdMaskMock->expects($this->once())->method('setQuoteId')->with($replaceQuoteId)->willReturnSelf();
        $quoteIdMaskMock->expects($this->once())->method('save');

        $quoteIdMaskFactoryMock = $this->createPartialMock(\Magento\Quote\Model\QuoteIdMaskFactory::class, ['create']);
        $quoteIdMaskFactoryMock->expects($this->once())->method('create')->willReturn($quoteIdMaskMock);

        $session = $this->_helper->getObject(
            \Magento\Checkout\Model\Session::class,
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

    public function testClearStorage()
    {
        $storage = $this->getMockBuilder(\Magento\Framework\Session\Storage::class)
            ->disableOriginalConstructor()
            ->setMethods(['unsetData'])
            ->getMock();
        $storage->expects($this->once())
            ->method('unsetData');

        $session = $this->_helper->getObject(
            \Magento\Checkout\Model\Session::class,
            [
                'storage' => $storage
            ]
        );

        $this->assertInstanceOf(\Magento\Checkout\Model\Session::class, $session->clearStorage());
        $this->assertFalse($session->hasQuote());
    }

    public function testResetCheckout()
    {
        /** @var $session \Magento\Checkout\Model\Session */
        $session = $this->_helper->getObject(
            \Magento\Checkout\Model\Session::class, [
            'storage' => new \Magento\Framework\Session\Storage()
        ]);
        $session->resetCheckout();
        $this->assertEquals(\Magento\Checkout\Model\Session::CHECKOUT_STATE_BEGIN, $session->getCheckoutState());
    }

    public function testGetStepData()
    {
        $stepData = [
            'simple' => 'data',
            'complex' => [
                'key' => 'value',
            ],
        ];
        /** @var $session \Magento\Checkout\Model\Session */
        $session = $this->_helper->getObject(
            \Magento\Checkout\Model\Session::class, [
            'storage' => new \Magento\Framework\Session\Storage()
        ]);
        $session->setSteps($stepData);
        $this->assertEquals($stepData, $session->getStepData());
        $this->assertFalse($session->getStepData('invalid_key'));
        $this->assertEquals($stepData['complex'], $session->getStepData('complex'));
        $this->assertFalse($session->getStepData('simple', 'invalid_sub_key'));
        $this->assertEquals($stepData['complex']['key'], $session->getStepData('complex', 'key'));
    }

    public function testSetStepData()
    {
        $stepData = [
            'complex' => [
                'key' => 'value',
            ],
        ];
        /** @var $session \Magento\Checkout\Model\Session */
        $session = $this->_helper->getObject(
            \Magento\Checkout\Model\Session::class, [
            'storage' => new \Magento\Framework\Session\Storage()
        ]);
        $session->setSteps($stepData);

        $session->setStepData('complex', 'key2', 'value2');
        $session->setStepData('simple', ['key' => 'value']);
        $session->setStepData('simple', 'key2', 'value2');
        $expectedResult = [
            'complex' => [
                'key' => 'value',
                'key2' => 'value2',
            ],
            'simple' => [
                'key' => 'value',
                'key2' => 'value2',
            ],
        ];
        $this->assertEquals($expectedResult, $session->getSteps());
    }
}
