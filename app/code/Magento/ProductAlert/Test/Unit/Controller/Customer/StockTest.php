<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ProductAlert\Test\Unit\Controller\Customer;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\ProductAlert\Controller\Customer\Stock;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Magento\ProductAlert\Controller\Customer\Stock
 */
class StockTest extends TestCase
{
    /**
     * @var Stock
     */
    private $controller;

    /**
     * @var Redirect|MockObject
     */
    private $resultRedirectMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $resultFactoryMock = $this->createMock(ResultFactory::class);
        $this->resultRedirectMock = $this->createMock(Redirect::class);
        $contextMock = $this->createMock(Context::class);
        $contextMock->method('getResultFactory')->willReturn($resultFactoryMock);
        $resultFactoryMock->method('create')
            ->with(ResultFactory::TYPE_REDIRECT)
            ->willReturn($this->resultRedirectMock);
        $customerSessionMock = $this->createMock(Session::class);

        $this->controller = $objectManager->getObject(
            Stock::class,
            [
                'context' => $contextMock,
                'customerSession' => $customerSessionMock,
            ]
        );
    }

    public function testExecuteRedirectsToCombinedIndex(): void
    {
        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('productalert/customer/index')
            ->willReturnSelf();

        $this->assertSame($this->resultRedirectMock, $this->controller->execute());
    }
}
