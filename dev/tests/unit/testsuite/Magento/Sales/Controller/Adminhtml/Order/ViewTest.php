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
namespace Magento\Sales\Controller\Adminhtml\Order;

use Magento\Backend\App\Action;

/**
 * Magento Adminhtml Order Controller Test
 */
class ViewTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $_objectHelper;

    /**
     * Mock for \Magento\Sales\Model\Order
     *
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_orderMock;

    /**
     * Mock for \Magento\Framework\Message
     *
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_messageMock;

    /**
     * Mock for \Magento\Sales\Adminhtml\Controller\Order
     *
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_controllerMock;

    /**
     * setup mocks for all functions
     */
    public function setUp()
    {
        $this->_objectHelper = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->_orderMock = $this->getMockBuilder(
            '\Magento\Sales\Model\Order'
        )->disableOriginalConstructor()->setMethods(
            array('__wakeup', 'getRealOrderId')
        )->getMock();

        $this->_messageMock = $this->getMockBuilder(
            '\Magento\Framework\Message'
        )->disableOriginalConstructor()->setMethods(
            array('addError')
        )->getMock();

        $titleMock =
            $this->getMock('\Magento\Framework\App\Action\Title', array('__wakeup', 'add'), array(), '', false);
        $viewMock = $this->getMockForAbstractClass('\Magento\Framework\App\ViewInterface');

        /**
         * @TODO:
         *  - Methods of object under test MUST NOT be mocked
         *  - Protected properties MUST NOT be set from outside, inject via context passed to constructor instead
         */
        $this->_controllerMock = $this->getMockBuilder(
            '\Magento\Sales\Controller\Adminhtml\Order\View'
        )->disableOriginalConstructor()->setMethods(
            array('__wakeup', '_initOrder', '_initAction', '__', 'renderLayout', '_redirect')
        )->getMock();
        $this->_controllerMock->expects($this->any())->method('__')->will($this->returnArgument(0));

        $reflectionProperty = new \ReflectionProperty('\Magento\Sales\Controller\Adminhtml\Order', '_title');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->_controllerMock, $titleMock);
        $reflectionProperty->setAccessible(false);

        $reflectionProperty = new \ReflectionProperty('\Magento\Sales\Controller\Adminhtml\Order', '_view');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->_controllerMock, $viewMock);
        $reflectionProperty->setAccessible(false);

        $reflectionProperty = new \ReflectionProperty('\Magento\Sales\Controller\Adminhtml\Order', 'messageManager');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->_controllerMock, $this->_messageMock);
        $reflectionProperty->setAccessible(false);
    }

    /**
     * This function checks if the error is added to session in case of ACTION_FLAG_PRODUCTS_PERMISSION_DENIED
     * is set in Sales Order model
     */
    public function testViewActionWithError()
    {
        $msg = 'You need more permissions to view this item.';
        $this->_messageMock->expects($this->once())->method('addError')->with($this->equalTo($msg));
        $this->_controllerMock->expects(
            $this->once()
        )->method(
            '_initOrder'
        )->will(
            $this->returnValue($this->_orderMock)
        );
        $this->_controllerMock->expects(
            $this->once()
        )->method(
            '_initAction'
        )->will(
            $this->throwException(new \Magento\Framework\App\Action\Exception($msg))
        );
        $this->_orderMock->expects($this->never())->method('getRealOrderId');

        $this->_controllerMock->execute();
    }

    /**
     * This function checks if the error is added to session in case of ACTION_FLAG_PRODUCTS_PERMISSION_DENIED
     * is not set in Sales Order model
     */
    public function testViewActionWithoutError()
    {
        $this->_orderMock->setRealOrderId(1);
        $this->_controllerMock->expects(
            $this->once()
        )->method(
            '_initOrder'
        )->will(
            $this->returnValue($this->_orderMock)
        );
        $this->_messageMock->expects($this->never())->method('addError');
        $this->_orderMock->expects($this->once())->method('getRealOrderId')->will($this->returnValue(1));

        $this->_controllerMock->execute();
    }
}
