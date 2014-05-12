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
namespace Magento\Directory\Model\Currency;

class DefaultLocatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Directory\Model\Currency\DefaultLocator
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_configMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_storeManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_requestMock;

    protected function setUp()
    {
        $backendData = $this->getMock('Magento\Backend\Helper\Data', array(), array(), '', false);
        $this->_requestMock = $this->getMockForAbstractClass(
            'Magento\Framework\App\RequestInterface',
            array($backendData),
            '',
            false,
            false,
            true,
            array('getParam')
        );
        $this->_configMock = $this->getMock('\Magento\Framework\App\Config\ScopeConfigInterface');
        $this->_storeManagerMock = $this->getMock('Magento\Store\Model\StoreManager', array(), array(), '', false);
        $this->_model = new \Magento\Directory\Model\Currency\DefaultLocator(
            $this->_configMock,
            $this->_storeManagerMock
        );
    }

    public function testGetDefaultCurrencyReturnDefaultStoreDefaultCurrencyIfNoStoreIsSpecified()
    {
        $this->_configMock->expects($this->once())->method('getValue')->will($this->returnValue('storeCurrency'));
        $this->assertEquals('storeCurrency', $this->_model->getDefaultCurrency($this->_requestMock));
    }

    public function testGetDefaultCurrencyReturnStoreDefaultCurrency()
    {
        $this->_requestMock->expects(
            $this->any()
        )->method(
            'getParam'
        )->with(
            'store'
        )->will(
            $this->returnValue('someStore')
        );
        $storeMock = $this->getMock('Magento\Store\Model\Store', array(), array(), '', false);
        $storeMock->expects($this->once())->method('getBaseCurrencyCode')->will($this->returnValue('storeCurrency'));
        $this->_storeManagerMock->expects(
            $this->once()
        )->method(
            'getStore'
        )->with(
            'someStore'
        )->will(
            $this->returnValue($storeMock)
        );
        $this->assertEquals('storeCurrency', $this->_model->getDefaultCurrency($this->_requestMock));
    }

    public function testGetDefaultCurrencyReturnWebsiteDefaultCurrency()
    {
        $this->_requestMock->expects(
            $this->any()
        )->method(
            'getParam'
        )->will(
            $this->returnValueMap(array(array('store', null, ''), array('website', null, 'someWebsite')))
        );
        $websiteMock = $this->getMock('Magento\Store\Model\Website', array(), array(), '', false);
        $websiteMock->expects(
            $this->once()
        )->method(
            'getBaseCurrencyCode'
        )->will(
            $this->returnValue('websiteCurrency')
        );
        $this->_storeManagerMock->expects(
            $this->once()
        )->method(
            'getWebsite'
        )->with(
            'someWebsite'
        )->will(
            $this->returnValue($websiteMock)
        );
        $this->assertEquals('websiteCurrency', $this->_model->getDefaultCurrency($this->_requestMock));
    }

    public function testGetDefaultCurrencyReturnGroupDefaultCurrency()
    {
        $this->_requestMock->expects(
            $this->any()
        )->method(
            'getParam'
        )->will(
            $this->returnValueMap(
                array(array('store', null, ''), array('website', null, ''), array('group', null, 'someGroup'))
            )
        );
        $websiteMock = $this->getMock('Magento\Store\Model\Website', array(), array(), '', false);
        $websiteMock->expects(
            $this->once()
        )->method(
            'getBaseCurrencyCode'
        )->will(
            $this->returnValue('websiteCurrency')
        );

        $groupMock = $this->getMock('Magento\Store\Model\Group', array(), array(), '', false);
        $groupMock->expects($this->once())->method('getWebsite')->will($this->returnValue($websiteMock));

        $this->_storeManagerMock->expects(
            $this->once()
        )->method(
            'getGroup'
        )->with(
            'someGroup'
        )->will(
            $this->returnValue($groupMock)
        );
        $this->assertEquals('websiteCurrency', $this->_model->getDefaultCurrency($this->_requestMock));
    }
}
