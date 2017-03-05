<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Test\Unit\Controller;

use \Magento\Checkout\Controller\Onepage;

/**
 * Class OnepageTest
 * @package Magento\Checkout\Controller
 */
class OnepageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Onepage
     */
    protected $controller;

    /**
     * @var \Magento\Checkout\Model\Session | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Customer\Model\Session | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\App\Request\Http | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    /**
     * @var \Magento\Framework\App\Response\Http | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $response;

    /**
     * @var \Magento\Quote\Model\Quote | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quote;

    /**
     * @var \Magento\Framework\Event\Manager | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManager;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->request = $this->getMock(\Magento\Framework\App\Request\Http::class, [], [], '', false);
        $this->response = $this->getMock(\Magento\Framework\App\Response\Http::class, [], [], '', false);
        $this->quote = $this->getMock(\Magento\Quote\Model\Quote::class, [], [], '', false);
        $this->eventManager = $this->getMock(\Magento\Framework\Event\Manager::class, [], [], '', false);
        $this->customerSession = $this->getMock(\Magento\Customer\Model\Session::class, [], [], '', false);
        $this->checkoutSession = $this->getMock(\Magento\Checkout\Model\Session::class, [], [], '', false);
        $this->checkoutSession->expects($this->once())
            ->method('getQuote')
            ->willReturn($this->quote);

        $objectManagerMock = $this->getMock(\Magento\Framework\ObjectManager\ObjectManager::class, [], [], '', false);
        $objectManagerMock->expects($this->at(0))
            ->method('get')
            ->with(\Magento\Checkout\Model\Session::class)
            ->willReturn($this->checkoutSession);
        $objectManagerMock->expects($this->at(1))
            ->method('get')
            ->with(\Magento\Customer\Model\Session::class)
            ->willReturn($this->customerSession);

        $context = $this->getMock(\Magento\Framework\App\Action\Context::class, [], [], '', false);
        $context->expects($this->once())
            ->method('getObjectManager')
            ->willReturn($objectManagerMock);
        $context->expects($this->once())
            ->method('getRequest')
            ->willReturn($this->request);
        $context->expects($this->once())
            ->method('getResponse')
            ->willReturn($this->response);
        $context->expects($this->once())
            ->method('getEventManager')
            ->willReturn($this->eventManager);
        
        $this->controller = $objectManager->getObject(
            \Magento\Checkout\Test\Unit\Controller\Stub\OnepageStub::class,
            [
                'context' => $context
            ]
        );
    }

    public function testDispatch()
    {
        $this->request->expects($this->once())
            ->method('getActionName')
            ->willReturn('index');

        $this->assertEquals($this->response, $this->controller->dispatch($this->request));
    }
}
