<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App;

class RequestFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RequestFactory
     */
    protected $model;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    protected function setUp()
    {
        $this->objectManagerMock = $this->getMock('Magento\Framework\ObjectManagerInterface');
        $this->model = new RequestFactory($this->objectManagerMock);
    }

    /**
     * @covers \Magento\Framework\App\RequestFactory::__construct
     * @covers \Magento\Framework\App\RequestFactory::create
     */
    public function testCreate()
    {
        $arguments = ['some_key' => 'same_value'];

        $appRequest = $this->getMock('Magento\Framework\App\RequestInterface');

        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with('Magento\Framework\App\RequestInterface', $arguments)
            ->will($this->returnValue($appRequest));

        $this->assertEquals($appRequest, $this->model->create($arguments));
    }
}
