<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Core\Model\File\Storage;

class RequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Core\Model\File\Storage\Request
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_requestMock;

    /**
     * @var string
     */
    protected $_workingDir = '..var';

    /**
     * @var string
     */
    protected $_pathInfo = 'PathInfo';

    protected function setUp()
    {
        $path = '..PathInfo';
        $this->_requestMock = $this->getMock('\Magento\Framework\App\Request\Http', [], [], '', false);
        $this->_requestMock->expects($this->once())->method('getPathInfo')->will($this->returnValue($path));
        $this->_model = new \Magento\Core\Model\File\Storage\Request($this->_workingDir, $this->_requestMock);
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

    public function testGetFilePath()
    {
        $this->assertEquals($this->_workingDir . '/' . $this->_pathInfo, $this->_model->getFilePath());
    }
}
