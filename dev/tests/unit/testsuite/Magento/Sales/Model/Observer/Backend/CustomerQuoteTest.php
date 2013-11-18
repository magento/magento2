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

class CustomerQuoteTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\Observer\Backend\CustomerQuote
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_quoteMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_configMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_eventMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_storeManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_observerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_customerMock;

    protected function setUp()
    {
        $this->_quoteMock = $this->getMock('Magento\Sales\Model\Quote',
            array('setWebsite', 'loadByCustomer', 'getId', 'setCustomerGroupId', 'collectTotals'), array(), '', false
        );
        $this->_observerMock = $this->getMock('Magento\Event\Observer', array(), array(), '', false);
        $this->_storeManagerMock = $this->getMock('Magento\Core\Model\StoreManager', array(), array(), '', false);
        $this->_configMock = $this->getMock('Magento\Customer\Model\Config\Share', array(), array(), '', false);
        $this->_eventMock = $this->getMock('Magento\Event', array('getCustomer'), array(), '', false);
        $this->_customerMock = $this->getMock('Magento\Customer\Model\Customer', array(), array(), '', false);
        $this->_observerMock->expects($this->any())->method('getEvent')->will($this->returnValue($this->_eventMock));
        $this->_eventMock
            ->expects($this->once())
            ->method('getCustomer')
            ->will($this->returnValue($this->_customerMock));
        $this->_model = new \Magento\Sales\Model\Observer\Backend\CustomerQuote(
            $this->_storeManagerMock,
            $this->_configMock,
            $this->_quoteMock
        );
    }

    public function testDispatchIfCustomerDataEqual()
    {
        $this->_customerMock->expects($this->once())->method('getGroupId')->will($this->returnValue(1));
        $this->_customerMock->expects($this->once())->method('getOrigData')->will($this->returnValue(1));
        $this->_configMock->expects($this->never())->method('isWebsiteScope');
        $this->_model->dispatch($this->_observerMock);
    }

    public function testDispatchIfWebsiteScopeEnable()
    {
        $this->_customerMock->expects($this->once())->method('getGroupId')->will($this->returnValue(1));
        $this->_customerMock->expects($this->once())->method('getOrigData')->will($this->returnValue(2));
        $this->_configMock->expects($this->any())->method('isWebsiteScope')->will($this->returnValue(true));
        $this->_customerMock->expects($this->any())->method('getWebsiteId');
        $this->_storeManagerMock->expects($this->never())->method('getWebsites');
        $this->_model->dispatch($this->_observerMock);
    }

    public function testDispatchIfWebsiteScopeDisable()
    {
        $websiteMock = $this->getMock('Magento\Core\Model\Website', array(), array(), '', false);
        $this->_customerMock->expects($this->once())->method('getGroupId')->will($this->returnValue(1));
        $this->_customerMock->expects($this->once())->method('getOrigData')->will($this->returnValue(2));
        $this->_configMock->expects($this->any())->method('isWebsiteScope')->will($this->returnValue(false));
        $this->_customerMock->expects($this->never())->method('getWebsiteId');
        $this->_storeManagerMock
            ->expects($this->once())
            ->method('getWebsites')
            ->will($this->returnValue(array($websiteMock)));
        $this->_storeManagerMock->expects($this->never())->method('getWebsite');
        $this->_model->dispatch($this->_observerMock);
    }

    /**
     * @dataProvider dispatchIfArrayExistDataProvider
     * @param bool $quoteId
     */
    public function testDispatchIfArrayExist($quoteId)
    {
        $websiteMock = $this->getMock('Magento\Core\Model\Website', array(), array(), '', false);
        $this->_customerMock->expects($this->any())->method('getGroupId')->will($this->returnValue(1));
        $this->_customerMock->expects($this->once())->method('getOrigData')->will($this->returnValue(2));
        $this->_configMock->expects($this->any())->method('isWebsiteScope')->will($this->returnValue(true));
        $this->_customerMock->expects($this->never())->method('getWebsiteId');
        $this->_storeManagerMock
            ->expects($this->once())
            ->method('getWebsite')
            ->will($this->returnValue(array($websiteMock)));
        $this->_quoteMock->expects($this->once())->method('setWebsite');
        $this->_quoteMock->expects($this->once())->method('loadByCustomer')->with($this->_customerMock);
        $this->_quoteMock->expects(($this->once()))->method('getId')->will($this->returnValue($quoteId));
        $this->_quoteMock->expects($this->any())->method('save');
        $this->_model->dispatch($this->_observerMock);
    }

    public function dispatchIfArrayExistDataProvider()
    {
        return array(
            array(true),
            array(false),
        );
    }
}
