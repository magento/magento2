<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for \Magento\Checkout\Model\Session
 */
namespace Magento\Checkout\Model;

include __DIR__ . '/../_files/session.php';
class SessionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $_helper;

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
            array('create')
        )->getMock();
        $orderFactory->expects($this->once())->method('create')->will($this->returnValue($orderMock));

        $messageCollectionFactory = $this->getMockBuilder(
            'Magento\Message\CollectionFactory'
        )->disableOriginalConstructor()->getMock();
        $quoteFactory = $this->getMockBuilder(
            'Magento\Sales\Model\QuoteFactory'
        )->disableOriginalConstructor()->getMock();

        $appState = $this->getMock('\Magento\App\State', array(), array(), '', false);
        $appState->expects($this->any())->method('isInstalled')->will($this->returnValue(true));

        $request = $this->getMock('\Magento\App\Request\Http', array(), array(), '', false);
        $request->expects($this->any())->method('getHttpHost')->will($this->returnValue(array()));

        $constructArguments = $this->_helper->getConstructArguments(
            'Magento\Checkout\Model\Session',
            array(
                'request' => $this->getMock('Magento\App\RequestInterface', array(), array(), '', false),
                'orderFactory' => $orderFactory,
                'messageCollectionFactory' => $messageCollectionFactory,
                'quoteFactory' => $quoteFactory,
                'storage' => new \Magento\Session\Storage()
            )
        );
        /** @var \Magento\Checkout\Model\Session $session */
        $session = $this->_helper->getObject('Magento\Checkout\Model\Session', $constructArguments);
        $session->setLastRealOrderId($orderId);

        $this->assertSame($orderMock, $session->getLastRealOrder());
        if ($orderId == $incrementId) {
            $this->assertSame($orderMock, $session->getLastRealOrder());
        }
    }

    /**
     * @return array
     */
    public function getLastRealOrderDataProvider()
    {
        return array(
            array(null, 1, $this->_getOrderMock(1, null)),
            array(1, 1, $this->_getOrderMock(1, 1)),
            array(1, null, $this->_getOrderMock(null, 1))
        );
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
            array('getIncrementId', 'loadByIncrementId', '__sleep', '__wakeup')
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
        $storage = new \Magento\Session\Storage('default', array($paramToClear => 'test_data'));
        /** @var \Magento\Checkout\Model\Session $session */
        $session = $this->_helper->getObject('Magento\Checkout\Model\Session', array('storage' => $storage));

        $session->clearHelperData();
        $this->assertNull($session->getData($paramToClear));
    }

    /**
     * @return array
     */
    public function clearHelperDataDataProvider()
    {
        return array(
            array('redirect_url'),
            array('last_order_id'),
            array('last_real_order_id'),
            array('additional_messages')
        );
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
            array('getId', 'loadByIncrementId', '__wakeup'),
            array(),
            '',
            false
        );
        $order->expects($this->once())->method('getId')->will($this->returnValue($hasOrderId ? 'order id' : null));
        $orderFactory = $this->getMock('Magento\Sales\Model\OrderFactory', array('create'), array(), '', false);
        $orderFactory->expects($this->once())->method('create')->will($this->returnValue($order));
        $quoteFactory = $this->getMock('Magento\Sales\Model\QuoteFactory', array('create'), array(), '', false);
        $storage = $this->getMock('Magento\Session\Storage', null);
        $store = $this->getMock('Magento\Core\Model\Store', array(), array(), '', false);
        $storeManager = $this->getMockForAbstractClass('Magento\Core\Model\StoreManagerInterface');
        $storeManager->expects($this->any())->method('getStore')->will($this->returnValue($store));
        $eventManager = $this->getMockForAbstractClass('Magento\Event\ManagerInterface');

        /** @var Session $session */
        $session = $this->_helper->getObject(
            'Magento\Checkout\Model\Session',
            array(
                'orderFactory' => $orderFactory,
                'quoteFactory' => $quoteFactory,
                'storage' => $storage,
                'storeManager' => $storeManager,
                'eventManager' => $eventManager
            )
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
                array('getId', 'save', 'setIsActive', 'setReservedOrderId', 'load', '__wakeup'),
                array(),
                '',
                false
            );
            $quote->expects(
                $this->any()
            )->method(
                'getId'
            )->will(
                $this->returnValue($hasQuoteId ? $anotherQuoteId : null)
            );
            $quote->expects(
                $this->any()
            )->method(
                'load'
            )->with(
                $this->equalTo($quoteId)
            )->will(
                $this->returnValue($quote)
            );
            $quoteFactory->expects($this->once())->method('create')->will($this->returnValue($quote));
            if ($hasQuoteId) {
                $eventManager->expects(
                    $this->once()
                )->method(
                    'dispatch'
                )->with(
                    'restore_quote',
                    array('order' => $order, 'quote' => $quote)
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
                $quote->expects($this->once())->method('save');
            } else {
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
        return array(array(true, true), array(true, false), array(false, true), array(false, false));
    }
}
