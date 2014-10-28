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
namespace Magento\Backend\App\Area\Request;

class PathInfoProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\App\Request\PathInfoProcessor
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_backendHelperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_subjectMock;

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
        $this->_requestMock = $this->getMock('\Magento\Framework\App\RequestInterface');
        $this->_subjectMock = $this->getMock(
            '\Magento\Store\App\Request\PathInfoProcessor',
            array(),
            array(),
            '',
            false
        );
        $this->_backendHelperMock = $this->getMock('\Magento\Backend\Helper\Data', array(), array(), '', false);
        $this->_model = new \Magento\Backend\App\Request\PathInfoProcessor(
            $this->_subjectMock,
            $this->_backendHelperMock
        );
    }

    public function testProcessIfStoreCodeEqualToAreaFrontName()
    {
        $this->_backendHelperMock->expects(
            $this->once()
        )->method(
            'getAreaFrontName'
        )->will(
            $this->returnValue('storeCode')
        );
        $this->assertEquals($this->_pathInfo, $this->_model->process($this->_requestMock, $this->_pathInfo));
    }

    public function testProcessIfStoreCodeNotEqualToAreaFrontName()
    {
        $this->_backendHelperMock->expects(
            $this->once()
        )->method(
            'getAreaFrontName'
        )->will(
            $this->returnValue('store')
        );
        $this->_subjectMock->expects(
            $this->once()
        )->method(
            'process'
        )->with(
            $this->_requestMock,
            $this->_pathInfo
        )->will(
            $this->returnValue('Expected')
        );
        $this->assertEquals('Expected', $this->_model->process($this->_requestMock, $this->_pathInfo));
    }
}
