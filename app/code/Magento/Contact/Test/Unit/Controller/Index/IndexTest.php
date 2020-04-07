<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
 * Test covers Magento\Contact\Controller\Index\Index
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
    private $url;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->configMock = $this->getMockBuilder(ConfigInterface::class)->getMockForAbstractClass();

        $contextMock = $this->getMockBuilder(Context::class)
            ->setMethods(
                ['getRequest', 'getResponse', 'getResultFactory', 'getUrl']
            )->disableOriginalConstructor(
            )->getMock();

        $this->url = $this->getMockBuilder(UrlInterface::class)->getMockForAbstractClass();

        $contextMock->expects($this->any())
            ->method('getUrl')
            ->will($this->returnValue($this->url));

        $contextMock->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue(
                $this->getMockBuilder(RequestInterface::class)->getMockForAbstractClass()
            ));

        $contextMock->expects($this->any())
            ->method('getResponse')
            ->will($this->returnValue(
                $this->getMockBuilder(ResponseInterface::class)->getMockForAbstractClass()
            ));

        $this->resultFactoryMock = $this->getMockBuilder(ResultFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $contextMock->expects($this->once())
            ->method('getResultFactory')
            ->will($this->returnValue($this->resultFactoryMock));

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
        $resultStub = $this->getMockForAbstractClass(ResultInterface::class);
        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->with(ResultFactory::TYPE_PAGE)
            ->willReturn($resultStub);

        $this->assertSame($resultStub, $this->controller->execute());
    }
}
