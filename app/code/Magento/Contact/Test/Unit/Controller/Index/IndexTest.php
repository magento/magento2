<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Contact\Test\Unit\Controller\Index;

use Magento\Contact\Controller\Index\Index;
use Magento\Contact\Model\ConfigInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\UrlInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

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
     * @var ResultFactory|MockObject
     */
    protected $resultFactory;

    /**
     * @var UrlInterface|MockObject
     */
    private $url;

    protected function setUp(): void
    {
        $this->configMock = $this->getMockBuilder(ConfigInterface::class)->getMockForAbstractClass();

        $context = $this->getMockBuilder(
            Context::class
        )->setMethods(
            ['getRequest', 'getResponse', 'getResultFactory', 'getUrl']
        )->disableOriginalConstructor(
        )->getMock();

        $this->url = $this->getMockBuilder(UrlInterface::class)->getMockForAbstractClass();

        $context->expects($this->any())
            ->method('getUrl')
            ->will($this->returnValue($this->url));

        $context->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue(
                $this->getMockBuilder(RequestInterface::class)->getMockForAbstractClass()
            ));

        $context->expects($this->any())
            ->method('getResponse')
            ->will($this->returnValue(
                $this->getMockBuilder(ResponseInterface::class)->getMockForAbstractClass()
            ));

        $this->resultFactory = $this->getMockBuilder(
            ResultFactory::class
        )->disableOriginalConstructor(
        )->getMock();

        $context->expects($this->once())
            ->method('getResultFactory')
            ->will($this->returnValue($this->resultFactory));

        $this->controller = new Index(
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
