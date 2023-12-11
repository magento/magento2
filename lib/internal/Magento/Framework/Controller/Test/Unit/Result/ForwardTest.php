<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Controller\Test\Unit\Result;

use Magento\Framework\App\Request\Http;
use Magento\Framework\Controller\Result\Forward;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ForwardTest extends TestCase
{
    /** @var Forward */
    protected $forward;

    /** @var Http|MockObject */
    protected $requestInterface;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->requestInterface = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->forward = $this->objectManagerHelper->getObject(
            Forward::class,
            [
                'request' => $this->requestInterface
            ]
        );
    }

    public function testSetModule()
    {
        $module = 'test_module';
        $this->assertInstanceOf(
            Forward::class,
            $this->forward->setModule($module)
        );
    }

    public function testSetController()
    {
        $controller = 'test_controller';
        $this->assertInstanceOf(
            Forward::class,
            $this->forward->setController($controller)
        );
    }

    public function testSetParams()
    {
        $params = ['param1', 'param2', 3];
        $this->assertInstanceOf(
            Forward::class,
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
            Forward::class,
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
            Forward::class,
            $this->forward->forward($action)
        );
    }
}
