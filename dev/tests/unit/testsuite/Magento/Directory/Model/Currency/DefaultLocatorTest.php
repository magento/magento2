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
 * @category    Magento
 * @package     Magento_Directory
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
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
    protected $_appMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_requestMock;

    protected function setUp()
    {
        $backendData = $this->getMock('Magento\Backend\Helper\Data', array(), array(), '', false);
        $this->_requestMock = $this->getMockForAbstractClass('Magento\App\RequestInterface',
            array($backendData), '', false, false, true, array('getParam'));
        $this->_appMock = $this->getMock('Magento\Core\Model\App', array(), array(), '', false);
        $this->_model = new \Magento\Directory\Model\Currency\DefaultLocator($this->_appMock);
    }

    public function testGetDefaultCurrencyReturnDefaultStoreDefaultCurrencyIfNoStoreIsSpecified()
    {
        $storeMock = $this->getMock('Magento\Core\Model\Store', array(), array(), '', false);
        $storeMock->expects($this->once())->method('getBaseCurrencyCode')->will($this->returnValue('storeCurrency'));
        $this->_appMock->expects($this->once())->method('getStore')->will($this->returnValue($storeMock));
        $this->assertEquals('storeCurrency', $this->_model->getDefaultCurrency($this->_requestMock));
    }

    public function testGetDefaultCurrencyReturnStoreDefaultCurrency()
    {
        $this->_requestMock->expects($this->any())->method('getParam')->with('store')
            ->will($this->returnValue('someStore'));
        $storeMock = $this->getMock('Magento\Core\Model\Store', array(), array(), '', false);
        $storeMock->expects($this->once())->method('getBaseCurrencyCode')->will($this->returnValue('storeCurrency'));
        $this->_appMock->expects($this->once())->method('getStore')->with('someStore')
            ->will($this->returnValue($storeMock));
        $this->assertEquals('storeCurrency', $this->_model->getDefaultCurrency($this->_requestMock));
    }

    public function testGetDefaultCurrencyReturnWebsiteDefaultCurrency()
    {
        $this->_requestMock->expects($this->any())->method('getParam')
            ->will($this->returnValueMap(
                array(array('store', null, ''), array('website', null, 'someWebsite')))
            );
        $websiteMock = $this->getMock('Magento\Core\Model\Website', array(), array(), '', false);
        $websiteMock->expects($this->once())->method('getBaseCurrencyCode')
            ->will($this->returnValue('websiteCurrency'));
        $this->_appMock->expects($this->once())->method('getWebsite')->with('someWebsite')
            ->will($this->returnValue($websiteMock));
        $this->assertEquals('websiteCurrency', $this->_model->getDefaultCurrency($this->_requestMock));
    }

    public function testGetDefaultCurrencyReturnGroupDefaultCurrency()
    {
        $this->_requestMock->expects($this->any())->method('getParam')
            ->will($this->returnValueMap(
                    array(array('store', null, ''), array('website', null, ''), array('group', null, 'someGroup'))
                )
            );
        $websiteMock = $this->getMock('Magento\Core\Model\Website', array(), array(), '', false);
        $websiteMock->expects($this->once())->method('getBaseCurrencyCode')
            ->will($this->returnValue('websiteCurrency'));

        $groupMock = $this->getMock('Magento\Core\Model\Store\Group', array(), array(), '', false);
        $groupMock->expects($this->once())->method('getWebsite')
            ->will($this->returnValue($websiteMock));

        $this->_appMock->expects($this->once())->method('getGroup')->with('someGroup')
            ->will($this->returnValue($groupMock));
        $this->assertEquals('websiteCurrency', $this->_model->getDefaultCurrency($this->_requestMock));
    }
}

