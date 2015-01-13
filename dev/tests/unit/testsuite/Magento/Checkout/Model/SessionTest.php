<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\Checkout\Model\Session
 */
namespace Magento\Checkout\Model;

class SessionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $_helper;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_session;

    public function setUp()
    {
        $this->_helper = new \Magento\TestFramework\Helper\ObjectManager($this);
    }

    /**
     * @param int|null $orderId
     * @param int|null $incrementId
     * @param \Magento\Sales\Model\Order|\PHPUnit_Framework_MockObject_MockObject $orderMock
     * @dataProvider getLastRealOrderDataProvider
     */
    public function testGetLastRealOrder($orderId, $incrementId, $orderMock)
    {
        $orderFactory = $this->getMockBuilder(
            'Magento\Sales\Model\OrderFactory'
        )->disableOriginalConstructor()->setMethods(
            ['create']
        )->getMock();
        $orderFactory->expects($this->once())->method('create')->will($this->returnValue($orderMock));

        $messageCollectionFactory = $this->getMockBuilder(
            'Magento\Framework\Message\CollectionFactory'
        )->disableOriginalConstructor()->getMock();
        $quoteRepository = $this->getMockBuilder(
            'Magento\Sales\Model\QuoteRepository'
        )->disableOriginalConstructor()->getMock();

        $appState = $this->getMock('\Magento\Framework\App\State', [], [], '', false);
        $appState->expects($this->any())->method('isInstalled')->will($this->returnValue(true));

        $request = $this->getMock('\Magento\Framework\App\Request\Http', [], [], '', false);
        $request->expects($this->any())->method('getHttpHost')->will($this->returnValue([]));

        $constructArguments = $this->_helper->getConstructArguments(
            'Magento\Checkout\Model\Session',
            [
                'request' => $request,
                'orderFactory' => $orderFactory,
                'messageCollectionFactory' => $messageCollectionFactory,
                'quoteRepository' => $quoteRepository,
                'storage' => new \Magento\Framework\Session\Storage()
            ]
        );
        $this->_session = $this->_helper->getObject('Magento\Checkout\Model\Session', $constructArguments);
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
            'Magento\Sales\Model\Order'
        )->disableOriginalConstructor()->setMethods(
            ['getIncrementId', 'loadByIncrementId', '__sleep', '__wakeup']
        )->getMock();

        $order->expects($this->once())->method('getIncrementId')->will($this->returnValue($incrementId));

        if ($orderId) {
            $order->expects($this->once())->method('loadByIncrementId')->with($orderId);
        }

        if ($orderId == $incrementId) {
            $order->expects($this->once())->method('getIncrementId')->will($this->returnValue($incrementId));
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
        $this->_session = $this->_helper->getObject('Magento\Checkout\Model\Session', ['storage' => $storage]);

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
        $order = $this->getMock(
            'Magento\Sales\Model\Order',
            ['getId', 'loadByIncrementId', '__wakeup'],
            [],
            '',
            false
        );
        $order->expects($this->once())->method('getId')->will($this->returnValue($hasOrderId ? 'order id' : null));
        $orderFactory = $this->getMock('Magento\Sales\Model\OrderFactory', ['create'], [], '', false);
        $orderFactory->expects($this->once())->method('create')->will($this->returnValue($order));
        $quoteRepository = $this->getMock(
            'Magento\Sales\Model\QuoteRepository',
            ['get', 'save'],
            [],
            '',
            false
        );
        $storage = $this->getMock('Magento\Framework\Session\Storage', null);
        $store = $this->getMock('Magento\Store\Model\Store', [], [], '', false);
        $storeManager = $this->getMockForAbstractClass('Magento\Store\Model\StoreManagerInterface');
        $storeManager->expects($this->any())->method('getStore')->will($this->returnValue($store));
        $eventManager = $this->getMockForAbstractClass('Magento\Framework\Event\ManagerInterface');

        /** @var Session $session */
        $session = $this->_helper->getObject(
            'Magento\Checkout\Model\Session',
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
            $quote = $this->getMock(
                'Magento\Sales\Model\Quote',
                ['setIsActive', 'getId', 'setReservedOrderId', '__wakeup'],
                [],
                '',
                false
            );
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
        $quote = $this->getMockBuilder('Magento\Sales\Model\Quote')
            ->disableOriginalConstructor()
            ->getMock();
        $session = $this->_helper->getObject('Magento\Checkout\Model\Session', ['quote' => $quote]);
        $this->assertFalse($session->hasQuote());
    }

    public function testReplaceQuote()
    {
        $replaceQuoteId = 3;
        $websiteId = 1;

        $store = $this->getMockBuilder('Magento\Store\Model\Store')
            ->disableOriginalConstructor()
            ->setMethods(['getWebsiteId', '__wakeup'])
            ->getMock();
        $store->expects($this->exactly(2))
            ->method('getWebsiteId')
            ->will($this->returnValue($websiteId));

        $storeManager = $this->getMockForAbstractClass('Magento\Store\Model\StoreManagerInterface');
        $storeManager->expects($this->any())
            ->method('getStore')
            ->will($this->returnValue($store));

        $quote = $this->getMockBuilder('Magento\Sales\Model\Quote')
            ->disableOriginalConstructor()
            ->getMock();
        $quote->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($replaceQuoteId));

        $storage = $this->getMockBuilder('Magento\Framework\Session\Storage')
            ->disableOriginalConstructor()
            ->setMethods(['setData', 'getData'])
            ->getMock();
        $storage->expects($this->once())
            ->method('setData')
            ->with('quote_id_' . $websiteId, $replaceQuoteId);
        $storage->expects($this->once())
            ->method('getData')
            ->with('quote_id_' . $websiteId)
            ->will($this->returnValue($replaceQuoteId));;

        $session = $this->_helper->getObject(
            'Magento\Checkout\Model\Session',
            [
                'storeManager' => $storeManager,
                'storage' => $storage
            ]
        );

        $session->replaceQuote($quote);

        $this->assertSame($quote, $session->getQuote());
        $this->assertEquals($replaceQuoteId, $session->getQuoteId());
    }

    public function testClearStorage()
    {
        $storage = $this->getMockBuilder('Magento\Framework\Session\Storage')
            ->disableOriginalConstructor()
            ->setMethods(['unsetData'])
            ->getMock();
        $storage->expects($this->once())
            ->method('unsetData');

        $session = $this->_helper->getObject(
            'Magento\Checkout\Model\Session',
            [
                'storage' => $storage
            ]
        );

        $this->assertInstanceOf('Magento\Checkout\Model\Session', $session->clearStorage());
        $this->assertFalse($session->hasQuote());
    }

    public function testResetCheckout()
    {
        /** @var $session \Magento\Checkout\Model\Session */
        $session = $this->_helper->getObject('Magento\Checkout\Model\Session', [
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
        $session = $this->_helper->getObject('Magento\Checkout\Model\Session', [
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
        $session = $this->_helper->getObject('Magento\Checkout\Model\Session', [
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
