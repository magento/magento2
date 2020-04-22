<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Test\Unit\Controller;

use Magento\Checkout\Controller\Onepage;
use Magento\Checkout\Model\Session;
use Magento\Checkout\Test\Unit\Controller\Stub\OnepageStub;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Event\Manager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\Quote;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class OnepageTest extends TestCase
{
    /**
     * @var Onepage
     */
    protected $controller;

    /**
     * @var Session|MockObject
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Customer\Model\Session|MockObject
     */
    protected $customerSession;

    /**
     * @var Http|MockObject
     */
    protected $request;

    /**
     * @var \Magento\Framework\App\Response\Http|MockObject
     */
    protected $response;

    /**
     * @var Quote|MockObject
     */
    protected $quote;

    /**
     * @var Manager|MockObject
     */
    protected $eventManager;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->request = $this->createMock(Http::class);
        $this->response = $this->createMock(\Magento\Framework\App\Response\Http::class);
        $this->quote = $this->createMock(Quote::class);
        $this->eventManager = $this->createMock(Manager::class);
        $this->customerSession = $this->createMock(\Magento\Customer\Model\Session::class);
        $this->checkoutSession = $this->createMock(Session::class);
        $this->checkoutSession->expects($this->once())
            ->method('getQuote')
            ->willReturn($this->quote);

        $objectManagerMock = $this->createMock(\Magento\Framework\ObjectManager\ObjectManager::class);
        $objectManagerMock->expects($this->at(0))
            ->method('get')
            ->with(Session::class)
            ->willReturn($this->checkoutSession);
        $objectManagerMock->expects($this->at(1))
            ->method('get')
            ->with(\Magento\Customer\Model\Session::class)
            ->willReturn($this->customerSession);

        $context = $this->createMock(Context::class);
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
            OnepageStub::class,
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
