<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MediaStorage\Test\Unit\Model\File\Storage;

class RequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\MediaStorage\Model\File\Storage\Request
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_requestMock;

    /**
     * @var string
     */
    protected $_pathInfo = 'PathInfo';

    protected function setUp()
    {
        $path = '..PathInfo';
        $this->_requestMock = $this->getMock('\Magento\Framework\App\Request\Http', [], [], '', false);
        $this->_requestMock->expects($this->once())->method('getPathInfo')->will($this->returnValue($path));
        $this->_model = new \Magento\MediaStorage\Model\File\Storage\Request($this->_requestMock);
    }

    protected function tearDown()
    {
        unset($this->_model);
        unset($this->_requestMock);
    }

    public function testGetPathInfo()
    {
        $this->assertEquals($this->_pathInfo, $this->_model->getPathInfo());
    }
}
