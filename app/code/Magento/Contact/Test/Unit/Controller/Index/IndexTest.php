<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Contact\Test\Unit\Controller\Index;

use Magento\Contact\Controller\Index\Index;
use Magento\Contact\Model\ConfigInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\UrlInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Magento\Contact\Controller\Index\Index
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
     * @var ResultFactory|MockObject
     */
    private $resultFactoryMock;

    /**
     * @var UrlInterface|MockObject
     */
    private $urlMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->configMock = $this->createMock(ConfigInterface::class);

        $contextMock = $this->getMockBuilder(Context::class)
            ->onlyMethods(
                ['getRequest', 'getResponse', 'getResultFactory', 'getUrl']
            )->disableOriginalConstructor(
            )->getMock();

        $this->urlMock = $this->createMock(UrlInterface::class);

        $contextMock->expects($this->any())
            ->method('getUrl')
            ->willReturn($this->urlMock);

        $contextMock->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->createMock(RequestInterface::class));

        $contextMock->expects($this->any())
            ->method('getResponse')
            ->willReturn($this->createMock(ResponseInterface::class));

        $this->resultFactoryMock = $this->createMock(ResultFactory::class);

        $contextMock->expects($this->once())
            ->method('getResultFactory')
            ->willReturn($this->resultFactoryMock);

        $this->controller = (new ObjectManagerHelper($this))->getObject(
            Index::class,
            [
                'context' => $contextMock,
                'contactsConfig' => $this->configMock
            ]
        );
    }

    /**
     * Test Execute Method
     */
    public function testExecute(): void
    {
        $resultStub = $this->createMock(ResultInterface::class);
        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->with(ResultFactory::TYPE_PAGE)
            ->willReturn($resultStub);

        $this->assertSame($resultStub, $this->controller->execute());
    }
}
