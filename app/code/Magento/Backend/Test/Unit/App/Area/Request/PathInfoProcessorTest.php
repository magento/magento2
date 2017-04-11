<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Test\Unit\App\Area\Request;

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
        $this->_requestMock = $this->getMock(\Magento\Framework\App\RequestInterface::class);
        $this->_subjectMock = $this->getMock(
            \Magento\Store\App\Request\PathInfoProcessor::class,
            [],
            [],
            '',
            false
        );
        $this->_backendHelperMock = $this->getMock(\Magento\Backend\Helper\Data::class, [], [], '', false);
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
