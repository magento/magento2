<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdvancedSearch\Test\Unit\Controller\Adminhtml\Search\System\Config;

use Magento\AdvancedSearch\Controller\Adminhtml\Search\System\Config\TestConnection;
use Magento\AdvancedSearch\Model\Client\ClientResolver;
use Magento\AdvancedSearch\Model\Client\ClientInterface;

/**
 * Test of TestConnection
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TestConnectionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\App\Request\Http|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $requestMock;

    /**
     * @var ClientResolver|\PHPUnit\Framework\MockObject\MockObject
     */
    private $clientResolverMock;

    /**
     * @var ClientInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $clientMock;

    /**
     * @var \Magento\Framework\Controller\Result\Json|\PHPUnit\Framework\MockObject\MockObject
     */
    private $resultJson;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $resultJsonFactory;

    /**
     * @var \Magento\Framework\Filter\StripTags|\PHPUnit\Framework\MockObject\MockObject
     */
    private $tagFilterMock;

    /**
     * @var TestConnection
     */
    private $controller;

    /**
     * Setup test function
     *
     * @return void
     */
    protected function setUp(): void
    {
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->requestMock = $this->createPartialMock(\Magento\Framework\App\Request\Http::class, ['getParams']);
        $responseMock = $this->createMock(\Magento\Framework\App\Response\Http::class);

        $context = $this->getMockBuilder(\Magento\Backend\App\Action\Context::class)
            ->setMethods(['getRequest', 'getResponse', 'getMessageManager', 'getSession'])
            ->setConstructorArgs(
                $helper->getConstructArguments(
                    \Magento\Backend\App\Action\Context::class,
                    [
                        'request' => $this->requestMock
                    ]
                )
            )
            ->getMock();
        $context->expects($this->once())->method('getRequest')->willReturn($this->requestMock);
        $context->expects($this->once())->method('getResponse')->willReturn($responseMock);

        $this->clientResolverMock = $this->getMockBuilder(\Magento\AdvancedSearch\Model\Client\ClientResolver::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->clientMock = $this->createMock(\Magento\AdvancedSearch\Model\Client\ClientInterface::class);

        $this->resultJson = $this->getMockBuilder(\Magento\Framework\Controller\Result\Json::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultJsonFactory = $this->getMockBuilder(\Magento\Framework\Controller\Result\JsonFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->tagFilterMock = $this->getMockBuilder(\Magento\Framework\Filter\StripTags::class)
            ->disableOriginalConstructor()
            ->setMethods(['filter'])
            ->getMock();

        $this->controller = new TestConnection(
            $context,
            $this->clientResolverMock,
            $this->resultJsonFactory,
            $this->tagFilterMock
        );
    }

    public function testExecuteEmptyEngine()
    {
        $this->requestMock->expects($this->once())->method('getParams')
            ->willReturn(['engine' => '']);

        $this->resultJsonFactory->expects($this->once())->method('create')
            ->willReturn($this->resultJson);

        $result = ['success' => false, 'errorMessage' => 'Missing search engine parameter.'];

        $this->resultJson->expects($this->once())->method('setData')
            ->with($this->equalTo($result));

        $this->controller->execute();
    }

    public function testExecute()
    {
        $this->requestMock->expects($this->once())->method('getParams')
            ->willReturn(['engine' => 'engineName']);

        $this->clientResolverMock->expects($this->once())->method('create')
            ->with($this->equalTo('engineName'))
            ->willReturn($this->clientMock);

        $this->clientMock->expects($this->once())->method('testConnection')
            ->willReturn(true);

        $this->resultJsonFactory->expects($this->once())->method('create')
            ->willReturn($this->resultJson);

        $result = ['success' => true, 'errorMessage' => ''];

        $this->resultJson->expects($this->once())->method('setData')
            ->with($this->equalTo($result));

        $this->controller->execute();
    }

    public function testExecutePingFailed()
    {
        $this->requestMock->expects($this->once())->method('getParams')
            ->willReturn(['engine' => 'engineName']);

        $this->clientResolverMock->expects($this->once())->method('create')
            ->with($this->equalTo('engineName'))
            ->willReturn($this->clientMock);

        $this->clientMock->expects($this->once())->method('testConnection')
            ->willReturn(false);

        $this->resultJsonFactory->expects($this->once())->method('create')
            ->willReturn($this->resultJson);

        $result = ['success' => false, 'errorMessage' => ''];

        $this->resultJson->expects($this->once())->method('setData')
            ->with($this->equalTo($result));

        $this->controller->execute();
    }
}
