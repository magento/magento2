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
 * @copyright Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Sales\Model\Observer\Backend;

class BillingAgreementTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\Observer\Backend\BillingAgreement
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_authorizationMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_observerMock;

    protected function setUp()
    {
        $this->_authorizationMock = $this->getMock('Magento\AuthorizationInterface');
        $this->_observerMock = $this->getMock('Magento\Event\Observer', array(), array(), '', false);
        $this->_model = new \Magento\Sales\Model\Observer\Backend\BillingAgreement(
            $this->_authorizationMock
        );
    }

    public function testDispatchIfMethodInterfaceNotAgreement()
    {
        $event = $this->getMock('Magento\Event', array('getMethodInstance'), array(), '', false);
        $this->_observerMock->expects($this->once())->method('getEvent')->will($this->returnValue($event));
        $event->expects($this->once())->method('getMethodInstance')->will($this->returnValue('some incorrect value'));
        $event->expects($this->never())->method('isAvailable');
        $this->_model->dispatch($this->_observerMock);
    }

    public function testDispatchIfMethodInterfaceAgreement()
    {
        $event = $this->getMock('Magento\Event', array('getMethodInstance', 'getResult'), array(), '', false);
        $this->_observerMock->expects($this->once())->method('getEvent')->will($this->returnValue($event));
        $methodInstance = $this->getMock('Magento\Paypal\Model\Method\Agreement', array(), array(), '', false);
        $event->expects($this->once())->method('getMethodInstance')->will($this->returnValue($methodInstance));
        $this->_authorizationMock->expects(
            $this->once())->method('isAllowed')->with('Magento_Sales::use')->will($this->returnValue(false)
        );
        $result = new \StdClass();
        $event->expects($this->once())->method('getResult')->will($this->returnValue($result));
        $this->_model->dispatch($this->_observerMock);
        $this->assertFalse($result->isAvailable);
    }
}
