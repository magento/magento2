<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Contact\Test\Unit\Controller;

use Magento\Contact\Controller\Index;
use Magento\Contact\Model\ConfigInterface;
use Magento\Contact\Test\Unit\Controller\Stub\IndexStub;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Magento\Contact\Controller\Index
 */
class IndexTest extends TestCase
{
    /**
     * @var Index
     */
    private $controller;

    /**
     * @var ConfigInterface|MockObject
     */
    private $configMock;

    /**
     * @var RequestInterface|MockObject
     */
    private $requestMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->configMock = $this->getMockBuilder(ConfigInterface::class)
            ->getMockForAbstractClass();

        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->getMockForAbstractClass();
        $responseMock = $this->getMockBuilder(ResponseInterface::class)
            ->getMockForAbstractClass();

        $contextMock = $this->getMockBuilder(Context::class)
            ->onlyMethods(['getRequest', 'getResponse'])
            ->disableOriginalConstructor()
            ->getMock();
        $contextMock->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->requestMock);

        $contextMock->expects($this->any())
            ->method('getResponse')
            ->willReturn($responseMock);

        $this->controller = (new ObjectManagerHelper($this))->getObject(
            IndexStub::class,
            [
                'context' => $contextMock,
                'contactsConfig' => $this->configMock
            ]
        );
    }

    /**
     * Dispatch test
     */
    public function testDispatch(): void
    {
        $this->expectException(NotFoundException::class);
        $this->configMock->method('isEnabled')->willReturn(false);
        $this->controller->dispatch($this->requestMock);
    }
}
