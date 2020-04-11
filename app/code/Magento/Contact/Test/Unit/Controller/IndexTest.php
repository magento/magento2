<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Contact\Test\Unit\Controller;

use Magento\Contact\Controller\Index;
use Magento\Contact\Model\ConfigInterface;
use Magento\Contact\Test\Unit\Controller\Stub\IndexStub;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class IndexTest extends TestCase
{
    /**
     * Controller instance
     *
     * @var Index
     */
    private $controller;

    /**
     * Module config instance
     *
     * @var ConfigInterface|MockObject
     */
    private $configMock;

    protected function setUp(): void
    {
        $this->configMock = $this->getMockBuilder(ConfigInterface::class)->getMockForAbstractClass();

        $context = $this->getMockBuilder(
            Context::class
        )->setMethods(
            ['getRequest', 'getResponse']
        )->disableOriginalConstructor(
        )->getMock();

        $context->expects($this->any())
            ->method('getRequest')
            ->will(
                $this->returnValue(
                    $this->getMockBuilder(RequestInterface::class)->getMockForAbstractClass()
                )
            );

        $context->expects($this->any())
            ->method('getResponse')
            ->will(
                $this->returnValue(
                    $this->getMockBuilder(ResponseInterface::class)->getMockForAbstractClass()
                )
            );

        $this->controller = new IndexStub(
            $context,
            $this->configMock
        );
    }

    /**
     * Dispatch test
     */
    public function testDispatch()
    {
        $this->expectException('Magento\Framework\Exception\NotFoundException');
        $this->configMock->method('isEnabled')->willReturn(false);

        $this->controller->dispatch(
            $this->getMockBuilder(RequestInterface::class)->getMockForAbstractClass()
        );
    }
}
