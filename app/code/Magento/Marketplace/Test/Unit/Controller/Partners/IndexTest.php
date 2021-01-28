<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Marketplace\Test\Unit\Controller\Partners;

class IndexTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Marketplace\Controller\Adminhtml\Partners\Index
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
     * @return \PHPUnit\Framework\MockObject\MockObject|\Magento\Marketplace\Controller\Adminhtml\Partners\Index
     */
    public function getControllerIndexMock($methods = null)
    {
        return $this->createPartialMock(\Magento\Marketplace\Controller\Adminhtml\Partners\Index::class, $methods);
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\View\LayoutFactory
     */
    public function getLayoutFactoryMock($methods = null)
    {
        return $this->createPartialMock(\Magento\Framework\View\LayoutFactory::class, $methods, []);
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\View\LayoutInterface
     */
    public function getLayoutMock()
    {
        return $this->getMockForAbstractClass(\Magento\Framework\View\LayoutInterface::class);
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\HTTP\PhpEnvironment\Response
     */
    public function getResponseMock($methods = null)
    {
        return $this->createPartialMock(\Magento\Framework\HTTP\PhpEnvironment\Response::class, $methods, []);
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\App\Request\Http
     */
    public function getRequestMock($methods = null)
    {
        return $this->createPartialMock(\Magento\Framework\App\Request\Http::class, $methods, []);
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\View\Element\BlockInterface
     */
    public function getBlockInterfaceMock()
    {
        return $this->getMockForAbstractClass(\Magento\Framework\View\Element\BlockInterface::class);
    }
}
