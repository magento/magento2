<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Controller\Result;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

class ForwardTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\Controller\Result\Forward */
    protected $forward;

    /** @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $requestInterface;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->requestInterface = $this->getMock(
            'Magento\Framework\App\RequestInterface',
            [
                'initForward',
                'getModuleName',
                'setModuleName',
                'getActionName',
                'setActionName',
                'getParam',
                'getCookie',
                'setDispatched',
                'setParams',
                'setControllerName'
            ],
            [],
            '',
            false
        );
        $this->forward = $this->objectManagerHelper->getObject(
            'Magento\Framework\Controller\Result\Forward',
            [
                'request' => $this->requestInterface
            ]
        );
    }

    public function testSetModule()
    {
        $module = 'test_module';
        $this->assertInstanceOf('Magento\Framework\Controller\Result\Forward', $this->forward->setModule($module));
    }

    public function testSetController()
    {
        $controller = 'test_controller';
        $this->assertInstanceOf(
            'Magento\Framework\Controller\Result\Forward',
            $this->forward->setController($controller)
        );
    }

    public function testSetParams()
    {
        $params = ['param1', 'param2', 3];
        $this->assertInstanceOf(
            'Magento\Framework\Controller\Result\Forward',
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
            'Magento\Framework\Controller\Result\Forward',
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
            'Magento\Framework\Controller\Result\Forward',
            $this->forward->forward($action)
        );
    }
}
