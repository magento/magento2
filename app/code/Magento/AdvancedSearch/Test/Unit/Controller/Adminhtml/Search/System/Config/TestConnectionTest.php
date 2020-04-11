<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdvancedSearch\Test\Unit\Controller\Adminhtml\Search\System\Config;

use PHPUnit\Framework\TestCase;
use Magento\Framework\App\Request\Http;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Filter\StripTags;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Backend\App\Action\Context;
use Magento\AdvancedSearch\Controller\Adminhtml\Search\System\Config\TestConnection;
use Magento\AdvancedSearch\Model\Client\ClientResolver;
use Magento\AdvancedSearch\Model\Client\ClientInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TestConnectionTest extends TestCase
{
    /**
     * @var Http|MockObject
     */
    protected $requestMock;

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
    private $resultJson;

    /**
     * @var JsonFactory|MockObject
     */
    private $resultJsonFactory;

    /**
     * @var StripTags|MockObject
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
        $helper = new ObjectManager($this);
        $this->requestMock = $this->createPartialMock(Http::class, ['getParams']);
        $responseMock = $this->createMock(\Magento\Framework\App\Response\Http::class);

        $context = $this->getMockBuilder(Context::class)
            ->setMethods(['getRequest', 'getResponse', 'getMessageManager', 'getSession'])
            ->setConstructorArgs(
                $helper->getConstructArguments(
                    Context::class,
                    [
                        'request' => $this->requestMock
                    ]
                )
            )
            ->getMock();
        $context->expects($this->once())->method('getRequest')->will($this->returnValue($this->requestMock));
        $context->expects($this->once())->method('getResponse')->will($this->returnValue($responseMock));

        $this->clientResolverMock = $this->getMockBuilder(ClientResolver::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->clientMock = $this->createMock(ClientInterface::class);

        $this->resultJson = $this->createMock(Json::class);

        $this->resultJsonFactory = $this->getMockBuilder(JsonFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->tagFilterMock = $this->getMockBuilder(StripTags::class)
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
            ->will($this->returnValue(['engine' => '']));

        $this->resultJsonFactory->expects($this->once())->method('create')
            ->will($this->returnValue($this->resultJson));

        $result = ['success' => false, 'errorMessage' => 'Missing search engine parameter.'];

        $this->resultJson->expects($this->once())->method('setData')
            ->with($this->equalTo($result));

        $this->controller->execute();
    }

    public function testExecute()
    {
        $this->requestMock->expects($this->once())->method('getParams')
            ->will($this->returnValue(['engine' => 'engineName']));

        $this->clientResolverMock->expects($this->once())->method('create')
            ->with($this->equalTo('engineName'))
            ->will($this->returnValue($this->clientMock));

        $this->clientMock->expects($this->once())->method('testConnection')
            ->will($this->returnValue(true));

        $this->resultJsonFactory->expects($this->once())->method('create')
            ->will($this->returnValue($this->resultJson));

        $result = ['success' => true, 'errorMessage' => ''];

        $this->resultJson->expects($this->once())->method('setData')
            ->with($this->equalTo($result));

        $this->controller->execute();
    }

    public function testExecutePingFailed()
    {
        $this->requestMock->expects($this->once())->method('getParams')
            ->will($this->returnValue(['engine' => 'engineName']));

        $this->clientResolverMock->expects($this->once())->method('create')
            ->with($this->equalTo('engineName'))
            ->will($this->returnValue($this->clientMock));

        $this->clientMock->expects($this->once())->method('testConnection')
            ->will($this->returnValue(false));

        $this->resultJsonFactory->expects($this->once())->method('create')
            ->will($this->returnValue($this->resultJson));

        $result = ['success' => false, 'errorMessage' => ''];

        $this->resultJson->expects($this->once())->method('setData')
            ->with($this->equalTo($result));

        $this->controller->execute();
    }
}
