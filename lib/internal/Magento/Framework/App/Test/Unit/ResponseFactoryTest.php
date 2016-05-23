<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Test\Unit;

class ResponseFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\ResponseFactory
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManagerMock;

    /**
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $_expectedObject;

    protected function setUp()
    {
        $this->_objectManagerMock = $this->getMock('Magento\Framework\ObjectManagerInterface');
        $this->_model = new \Magento\Framework\App\ResponseFactory($this->_objectManagerMock);
    }

    public function testCreate()
    {
        $this->_expectedObject = $this->getMockBuilder('\Magento\Framework\App\ResponseInterface')->getMock();
        $arguments = [['property' => 'value']];
        $this->_objectManagerMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            'Magento\Framework\App\ResponseInterface',
            $arguments
        )->will(
            $this->returnValue($this->_expectedObject)
        );

        $this->assertEquals($this->_expectedObject, $this->_model->create($arguments));
    }
}
