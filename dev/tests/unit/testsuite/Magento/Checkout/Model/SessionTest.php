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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for \Magento\Checkout\Model\Session
 */
namespace Magento\Checkout\Model;

class SessionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param int|null $orderId
     * @param int|null $incrementId
     * @param \Magento\Sales\Model\Order|\PHPUnit_Framework_MockObject_MockObject $orderMock
     * @dataProvider getLastRealOrderDataProvider
     */
    public function testGetLastRealOrder($orderId, $incrementId, $orderMock)
    {
        $orderFactory = $this->getMockBuilder('Magento\Sales\Model\OrderFactory')
            ->disableOriginalConstructor()
            ->setMethods(array('create'))
            ->getMock();
        $orderFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($orderMock));

        $messageCollFactory = $this->getMockBuilder('Magento\Core\Model\Message\CollectionFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $quoteFactory = $this->getMockBuilder('Magento\Sales\Model\QuoteFactory')
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $constructArguments = $objectManager->getConstructArguments(
            'Magento\Checkout\Model\Session',
            array(
                 'context' => $this->getMock('Magento\Core\Model\Session\Context', array(), array(), '', false),
                 'orderFactory' => $orderFactory,
                 'messageCollFactory' => $messageCollFactory,
                 'quoteFactory' => $quoteFactory,
            )
        );
        /** @var \Magento\Checkout\Model\Session $session */
        $session = $this->getMock('Magento\Checkout\Model\Session', array('init'), $constructArguments);
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
        $order = $this->getMockBuilder('Magento\Sales\Model\Order')
            ->disableOriginalConstructor()
            ->setMethods(array('getIncrementId', 'loadByIncrementId', '__sleep', '__wakeup'))
            ->getMock();

        $order->expects($this->once())
            ->method('getIncrementId')
            ->will($this->returnValue($incrementId));

        if ($orderId) {
            $order->expects($this->once())
            ->method('loadByIncrementId')
            ->with($orderId);
        }

        if ($orderId == $incrementId) {
            $order->expects($this->once())
                ->method('getIncrementId')
                ->will($this->returnValue($incrementId));
        }

        return $order;
    }
}
