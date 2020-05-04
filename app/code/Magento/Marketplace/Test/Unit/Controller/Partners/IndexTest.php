<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Marketplace\Test\Unit\Controller\Partners;

use Magento\Framework\App\Request\Http;
use Magento\Framework\HTTP\PhpEnvironment\Response;
use Magento\Framework\View\Element\BlockInterface;
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\View\LayoutInterface;
use Magento\Marketplace\Controller\Adminhtml\Partners\Index;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class IndexTest extends TestCase
{
    /**
     * @var MockObject|Index
     */
    private $partnersControllerMock;

    protected function setUp(): void
    {
        $this->partnersControllerMock = $this->getControllerIndexMock(
            [
                'getRequest',
                'getResponse',
                'getLayoutFactory'
            ]
        );
    }

    /**
     * @covers \Magento\Marketplace\Controller\Adminhtml\Partners\Index::execute
     */
    public function testExecute()
    {
        $requestMock = $this->getRequestMock(['isAjax']);
        $requestMock->expects($this->once())
            ->method('isAjax')
            ->willReturn(true);

        $this->partnersControllerMock->expects($this->once())
            ->method('getRequest')
            ->willReturn($requestMock);

        $layoutMock = $this->getLayoutMock();
        $blockMock = $this->getBlockInterfaceMock();
        $blockMock->expects($this->once())
            ->method('toHtml')
            ->willReturn('');

        $layoutMock->expects($this->once())
            ->method('createBlock')
            ->willReturn($blockMock);

        $layoutMockFactory = $this->getLayoutFactoryMock(['create']);
        $layoutMockFactory->expects($this->once())
            ->method('create')
            ->willReturn($layoutMock);

        $this->partnersControllerMock->expects($this->once())
            ->method('getLayoutFactory')
            ->willReturn($layoutMockFactory);

        $responseMock = $this->getResponseMock(['appendBody']);
        $responseMock->expects($this->once())
            ->method('appendBody')
            ->willReturn('');
        $this->partnersControllerMock->expects($this->once())
            ->method('getResponse')
            ->willReturn($responseMock);

        $this->partnersControllerMock->execute();
    }

    /**
     * Gets partners controller mock
     *
     * @return MockObject|Index
     */
    public function getControllerIndexMock($methods = null)
    {
        return $this->createPartialMock(Index::class, $methods);
    }

    /**
     * @return MockObject|LayoutFactory
     */
    public function getLayoutFactoryMock($methods = null)
    {
        return $this->createPartialMock(LayoutFactory::class, $methods, []);
    }

    /**
     * @return MockObject|LayoutInterface
     */
    public function getLayoutMock()
    {
        return $this->getMockForAbstractClass(LayoutInterface::class);
    }

    /**
     * @return MockObject|Response
     */
    public function getResponseMock($methods = null)
    {
        return $this->createPartialMock(Response::class, $methods, []);
    }

    /**
     * @return MockObject|Http
     */
    public function getRequestMock($methods = null)
    {
        return $this->createPartialMock(Http::class, $methods, []);
    }

    /**
     * @return MockObject|BlockInterface
     */
    public function getBlockInterfaceMock()
    {
        return $this->getMockForAbstractClass(BlockInterface::class);
    }
}
