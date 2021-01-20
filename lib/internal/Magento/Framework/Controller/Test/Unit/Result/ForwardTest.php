<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Controller\Test\Unit\Result;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class ForwardTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Framework\Controller\Result\Forward */
    protected $forward;

    /** @var \Magento\Framework\App\Request\Http|\PHPUnit\Framework\MockObject\MockObject */
    protected $requestInterface;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->requestInterface = $this->getMockBuilder(\Magento\Framework\App\Request\Http::class)
            ->disableOriginalConstructor()->getMock();
        $this->forward = $this->objectManagerHelper->getObject(
            \Magento\Framework\Controller\Result\Forward::class,
            [
                'request' => $this->requestInterface
            ]
        );
    }

    public function testSetModule()
    {
        $module = 'test_module';
        $this->assertInstanceOf(
            \Magento\Framework\Controller\Result\Forward::class,
            $this->forward->setModule($module)
        );
    }

    public function testSetController()
    {
        $controller = 'test_controller';
        $this->assertInstanceOf(
            \Magento\Framework\Controller\Result\Forward::class,
            $this->forward->setController($controller)
        );
    }

    public function testSetParams()
    {
        $params = ['param1', 'param2', 3];
        $this->assertInstanceOf(
            \Magento\Framework\Controller\Result\Forward::class,
            $this->forward->setParams($params)
        );
    }

    public function testForward()
    {
        $action = 'test_action';
        $this->requestInterface->expects($this->once())->method('initForward');
        $this->requestInterface->expects($this->once())->method('setActionName')->with($action);
        $this->requestInterface->expects($this->once())->method('setDispatched');
        $this->assertInstanceOf(
            \Magento\Framework\Controller\Result\Forward::class,
            $this->forward->forward($action)
        );
    }

    public function testForwardWithParams()
    {
        $action = 'test_action';
        $params = ['param1', 'param2', 3];
        $controller = 'test_controller';
        $module = 'test_module';
        $this->forward->setModule($module);
        $this->forward->setParams($params);
        $this->forward->setController($controller);
        $this->requestInterface->expects($this->once())->method('setParams')->with($params);
        $this->requestInterface->expects($this->once())->method('setControllerName')->with($controller);
        $this->requestInterface->expects($this->once())->method('setModuleName')->with($module);
        $this->requestInterface->expects($this->once())->method('initForward');
        $this->requestInterface->expects($this->once())->method('setActionName')->with($action);
        $this->requestInterface->expects($this->once())->method('setDispatched');
        $this->assertInstanceOf(
            \Magento\Framework\Controller\Result\Forward::class,
            $this->forward->forward($action)
        );
    }
}
