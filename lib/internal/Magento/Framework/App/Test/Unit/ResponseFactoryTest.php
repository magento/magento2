<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Test\Unit;

class ResponseFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\App\ResponseFactory
     */
    protected $_model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_objectManagerMock;

    /**
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $_expectedObject;

    protected function setUp(): void
    {
        $this->_objectManagerMock = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        $this->_model = new \Magento\Framework\App\ResponseFactory($this->_objectManagerMock);
    }

    public function testCreate()
    {
        $this->_expectedObject = $this->getMockBuilder(\Magento\Framework\App\ResponseInterface::class)->getMock();
        $arguments = [['property' => 'value']];
        $this->_objectManagerMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            \Magento\Framework\App\ResponseInterface::class,
            $arguments
        )->willReturn(
            $this->_expectedObject
        );

        $this->assertEquals($this->_expectedObject, $this->_model->create($arguments));
    }
}
