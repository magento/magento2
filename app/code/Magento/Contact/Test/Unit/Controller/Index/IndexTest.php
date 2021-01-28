<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Contact\Test\Unit\Controller\Index;

use Magento\Contact\Model\ConfigInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;

class IndexTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Contact\Controller\Index\Index
     */
    private $controller;

    /**
     * @var ConfigInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $configMock;

    /**
     * @var ResultFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $resultFactory;

    /**
     * @var \Magento\Framework\UrlInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $url;

    protected function setUp(): void
    {
        $this->configMock = $this->getMockBuilder(ConfigInterface::class)->getMockForAbstractClass();

        $context = $this->getMockBuilder(
            \Magento\Framework\App\Action\Context::class
        )->setMethods(
            ['getRequest', 'getResponse', 'getResultFactory', 'getUrl']
        )->disableOriginalConstructor(
        )->getMock();

        $this->url = $this->getMockBuilder(\Magento\Framework\UrlInterface::class)->getMockForAbstractClass();

        $context->expects($this->any())
            ->method('getUrl')
            ->willReturn($this->url);

        $context->expects($this->any())
            ->method('getRequest')
            ->willReturn(
                $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)->getMockForAbstractClass()
            );

        $context->expects($this->any())
            ->method('getResponse')
            ->willReturn(
                $this->getMockBuilder(\Magento\Framework\App\ResponseInterface::class)->getMockForAbstractClass()
            );

        $this->resultFactory = $this->getMockBuilder(
            ResultFactory::class
        )->disableOriginalConstructor(
        )->getMock();

        $context->expects($this->once())
            ->method('getResultFactory')
            ->willReturn($this->resultFactory);

        $this->controller = new \Magento\Contact\Controller\Index\Index(
            $context,
            $this->configMock
        );
    }

    public function testExecute()
    {
        $resultStub = $this->getMockForAbstractClass(ResultInterface::class);
        $this->resultFactory->expects($this->once())
            ->method('create')
            ->with(ResultFactory::TYPE_PAGE)
            ->willReturn($resultStub);

        $this->assertSame($resultStub, $this->controller->execute());
    }
}
