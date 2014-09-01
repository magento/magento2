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
namespace Magento\Store\App\Request;

class PathInfoProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Store\App\Request\PathInfoProcessor
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_storeManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_requestMock;

    /**
     * @var string
     */
    protected $_pathInfo = '/storeCode/node_one/';

    protected function setUp()
    {
        $this->_requestMock = $this->getMock(
            '\Magento\Framework\App\RequestInterface',
            array(
                'isDirectAccessFrontendName',
                'getModuleName',
                'setModuleName',
                'getActionName',
                'setActionName',
                'getParam',
                'getCookie'
            )
        );
        $this->_storeManagerMock = $this->getMock('\Magento\Store\Model\StoreManager', array(), array(), '', false);
        $this->_model = new \Magento\Store\App\Request\PathInfoProcessor($this->_storeManagerMock);
    }

    public function testProcessIfStoreExistsAndIsNotDirectAcccessToFrontName()
    {
        $store = $this->getMock('\Magento\Store\Model\Store', array(), array(), '', false);
        $this->_storeManagerMock->expects(
            $this->once()
        )->method(
            'getStores'
        )->with(
            false,
            true
        )->will(
            $this->returnValue(array('storeCode' => $store))
        );
        $store->expects($this->once())->method('isUseStoreInUrl')->will($this->returnValue(true));
        $this->_requestMock->expects(
            $this->once()
        )->method(
            'isDirectAccessFrontendName'
        )->with(
            'storeCode'
        )->will(
            $this->returnValue(false)
        );
        $this->_storeManagerMock->expects($this->once())->method('setCurrentStore')->with('storeCode');
        $this->assertEquals('/node_one/', $this->_model->process($this->_requestMock, $this->_pathInfo));
    }

    public function testProcessIfStoreExistsAndDirectAcccessToFrontName()
    {
        $store = $this->getMock('\Magento\Store\Model\Store', array(), array(), '', false);
        $this->_storeManagerMock->expects(
            $this->once()
        )->method(
            'getStores'
        )->with(
            false,
            true
        )->will(
            $this->returnValue(array('storeCode' => $store))
        );
        $store->expects($this->once())->method('isUseStoreInUrl')->will($this->returnValue(true));
        $this->_requestMock->expects(
            $this->once()
        )->method(
            'isDirectAccessFrontendName'
        )->with(
            'storeCode'
        )->will(
            $this->returnValue(true)
        );
        $this->_requestMock->expects($this->once())->method('setActionName')->with('noroute');
        $this->assertEquals($this->_pathInfo, $this->_model->process($this->_requestMock, $this->_pathInfo));
    }

    public function testProcessIfStoreIsEmpty()
    {
        $path = '/0/node_one/';
        $store = $this->getMock('\Magento\Store\Model\Store', array(), array(), '', false);
        $this->_storeManagerMock->expects(
            $this->once()
        )->method(
            'getStores'
        )->with(
            false,
            true
        )->will(
            $this->returnValue(array('0' => $store))
        );
        $store->expects($this->once())->method('isUseStoreInUrl')->will($this->returnValue(true));
        $this->_requestMock->expects(
            $this->once()
        )->method(
            'isDirectAccessFrontendName'
        )->with(
            '0'
        )->will(
            $this->returnValue(true)
        );
        $this->_requestMock->expects($this->never())->method('setActionName');
        $this->assertEquals($path, $this->_model->process($this->_requestMock, $path));
    }

    public function testProcessIfStoreCodeIsNotExist()
    {
        $store = $this->getMock('\Magento\Store\Model\Store', array(), array(), '', false);
        $this->_storeManagerMock->expects(
            $this->once()
        )->method(
            'getStores'
        )->with(
            false,
            true
        )->will(
            $this->returnValue(array('0' => $store))
        );
        $store->expects($this->never())->method('isUseStoreInUrl');
        $this->_requestMock->expects($this->never())->method('isDirectAccessFrontendName');
        $this->assertEquals($this->_pathInfo, $this->_model->process($this->_requestMock, $this->_pathInfo));
    }
}
