<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdvancedSearch\Test\Unit\Controller\Adminhtml\Search\System\Config;

use Magento\AdvancedSearch\Controller\Adminhtml\Search\System\Config\TestConnection;
use Magento\AdvancedSearch\Model\Client\ClientInterface;
use Magento\AdvancedSearch\Model\Client\ClientResolver;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\App\Response\Http as HttpResponse;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Filter\StripTags;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Magento\AdvancedSearch\Controller\Adminhtml\Search\System\Config\TestConnection
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TestConnectionTest extends TestCase
{
    /**
     * Testable Object
     *
     * @var TestConnection
     */
    private $controller;

    /**
     * @var HttpRequest|MockObject
     */
    private $requestMock;

    /**
     * @var ClientResolver|MockObject
     */
    private $clientResolverMock;

    /**
     * @var ClientInterface|MockObject
     */
    private $clientMock;

    /**
     * @var Json|MockObject
     */
    private $resultJsonMock;

    /**
     * @var JsonFactory|MockObject
     */
    private $resultJsonFactoryMock;

    /**
     * @var StripTags|MockObject
     */
    private $tagFilterMock;

    /**
     * Setup test function
     *
     * @return void
     */
    protected function setUp(): void
    {
        $helper = new ObjectManager($this);
        $this->requestMock = $this->createPartialMock(HttpRequest::class, ['getParams']);
        $responseMock = $this->createMock(HttpResponse::class);

        $context = $this->getMockBuilder(Context::class)
            ->onlyMethods(['getRequest', 'getResponse', 'getMessageManager', 'getSession'])
            ->setConstructorArgs(
                $helper->getConstructArguments(
                    Context::class,
                    [
                        'request' => $this->requestMock
                    ]
                )
            )
            ->getMock();
        $context->expects($this->once())->method('getRequest')->willReturn($this->requestMock);
        $context->expects($this->once())->method('getResponse')->willReturn($responseMock);

        $this->clientResolverMock = $this->getMockBuilder(ClientResolver::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $this->clientMock = $this->getMockForAbstractClass(ClientInterface::class);

        $this->resultJsonMock = $this->getMockBuilder(Json::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultJsonFactoryMock = $this->getMockBuilder(JsonFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $this->tagFilterMock = $this->getMockBuilder(StripTags::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['filter'])
            ->getMock();

        $this->controller = new TestConnection(
            $context,
            $this->clientResolverMock,
            $this->resultJsonFactoryMock,
            $this->tagFilterMock
        );
    }

    public function testExecuteEmptyEngine(): void
    {
        $this->requestMock->expects($this->once())->method('getParams')
            ->willReturn(['engine' => '']);

        $this->resultJsonFactoryMock->expects($this->once())->method('create')
            ->willReturn($this->resultJsonMock);

        $result = ['success' => false, 'errorMessage' => 'Missing search engine parameter.'];

        $this->resultJsonMock->expects($this->once())->method('setData')
            ->with($result);

        $this->controller->execute();
    }

    public function testExecute(): void
    {
        $this->requestMock->expects($this->once())->method('getParams')
            ->willReturn(['engine' => 'engineName']);

        $this->clientResolverMock->expects($this->once())->method('create')
            ->with('engineName')
            ->willReturn($this->clientMock);

        $this->clientMock->expects($this->once())->method('testConnection')
            ->willReturn(true);

        $this->resultJsonFactoryMock->expects($this->once())->method('create')
            ->willReturn($this->resultJsonMock);

        $result = ['success' => true, 'errorMessage' => ''];

        $this->resultJsonMock->expects($this->once())->method('setData')
            ->with($result);

        $this->controller->execute();
    }

    public function testExecutePingFailed(): void
    {
        $this->requestMock->expects($this->once())->method('getParams')
            ->willReturn(['engine' => 'engineName']);

        $this->clientResolverMock->expects($this->once())->method('create')
            ->with('engineName')
            ->willReturn($this->clientMock);

        $this->clientMock->expects($this->once())->method('testConnection')
            ->willReturn(false);

        $this->resultJsonFactoryMock->expects($this->once())->method('create')
            ->willReturn($this->resultJsonMock);

        $result = ['success' => false, 'errorMessage' => ''];

        $this->resultJsonMock->expects($this->once())->method('setData')
            ->with($result);

        $this->controller->execute();
    }
}
