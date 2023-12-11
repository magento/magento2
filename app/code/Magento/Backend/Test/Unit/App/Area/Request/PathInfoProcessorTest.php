<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\Test\Unit\App\Area\Request;

use Magento\Backend\App\Request\PathInfoProcessor;
use Magento\Backend\Helper\Data;
use Magento\Framework\App\RequestInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PathInfoProcessorTest extends TestCase
{
    /**
     * @var PathInfoProcessor
     */
    protected $_model;

    /**
     * @var MockObject
     */
    protected $_backendHelperMock;

    /**
     * @var MockObject
     */
    protected $_subjectMock;

    /**
     * @var MockObject
     */
    protected $_requestMock;

    /**
     * @var string
     */
    protected $_pathInfo = '/storeCode/node_one/';

    protected function setUp(): void
    {
        $this->_requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $this->_subjectMock = $this->createMock(\Magento\Store\App\Request\PathInfoProcessor::class);
        $this->_backendHelperMock = $this->createMock(Data::class);
        $this->_model = new PathInfoProcessor(
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
        )->willReturn(
            'storeCode'
        );
        $this->assertEquals($this->_pathInfo, $this->_model->process($this->_requestMock, $this->_pathInfo));
    }

    public function testProcessIfStoreCodeNotEqualToAreaFrontName()
    {
        $this->_backendHelperMock->expects(
            $this->once()
        )->method(
            'getAreaFrontName'
        )->willReturn(
            'store'
        );
        $this->_subjectMock->expects(
            $this->once()
        )->method(
            'process'
        )->with(
            $this->_requestMock,
            $this->_pathInfo
        )->willReturn(
            'Expected'
        );
        $this->assertEquals('Expected', $this->_model->process($this->_requestMock, $this->_pathInfo));
    }
}
