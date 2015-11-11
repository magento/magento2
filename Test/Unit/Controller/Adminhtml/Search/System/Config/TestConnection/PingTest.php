<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Test\Unit\Controller\Adminhtml\Search\System\Config\TestConnection;

use Magento\AdvancedSearch\Model\ClientOptionsInterface;
use Magento\Elasticsearch\Controller\Adminhtml\Search\System\Config\TestConnection\Ping;
use Magento\Elasticsearch\Model\Client\Elasticsearch;

class PingTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\Request\Http|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var Elasticsearch|\PHPUnit_Framework_MockObject_MockObject
     */
    private $client;

    /**
     * @var ClientInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $clientHelper;

    /**
     * @var \Magento\Framework\Controller\Result\Json|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultJson;

    /**
     * @var Ping
     */
    private $controller;

    /**
     * Setup test function
     *
     * @return void
     */
    protected function setUp()
    {
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->requestMock = $this->getMock('Magento\Framework\App\Request\Http', [], [], '', false);
        $responseMock = $this->getMock('Magento\Framework\App\Response\Http', [], [], '', false);

        $context = $this->getMock(
            'Magento\Backend\App\Action\Context',
            ['getRequest', 'getResponse', 'getMessageManager', 'getSession'],
            $helper->getConstructArguments(
                'Magento\Backend\App\Action\Context',
                [
                    'request' => $this->requestMock
                ]
            )
        );
        $context->expects($this->once())->method('getRequest')->will($this->returnValue($this->requestMock));
        $context->expects($this->once())->method('getResponse')->will($this->returnValue($responseMock));

        $this->client = $this->getMockBuilder('\Magento\Elasticsearch\Model\Client\Elasticsearch')
            ->disableOriginalConstructor()
            ->setMethods(['ping'])
            ->getMock();

        $clientFactory = $this->getMockBuilder('\Magento\AdvancedSearch\Model\Client\FactoryInterface')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $clientFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->client);

        $this->clientHelper = $this->getMockBuilder(ClientOptionsInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'prepareClientOptions'
                ]
            )
            ->getMock();

        $this->resultJson = $this->getMockBuilder('Magento\Framework\Controller\Result\Json')
            ->disableOriginalConstructor()
            ->getMock();

        $resultJsonFactory = $this->getMockBuilder('Magento\Framework\Controller\Result\JsonFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $resultJsonFactory->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($this->resultJson);

        $this->controller = new Ping($context, $clientFactory, $this->clientHelper, $resultJsonFactory);
    }

    /**
     * @dataProvider emptyParamDataProvider
     *
     * @param string $hostname
     * @param string $enableAuth
     * @param string $username
     * @param string $password
     * @return void
     */
    public function testExecuteEmptyParam($hostname, $enableAuth, $username, $password)
    {
        $options = [
            'hostname' => $hostname,
            'enableAuth' => $enableAuth,
            'username' => $username,
            'password' => $password,
            'timeout' => 0,
        ];
        $this->requestMock->expects($this->once())
            ->method('getParams')
            ->willReturn($options);
        $this->clientHelper->expects($this->once())
            ->method('prepareClientOptions')
            ->with($options)
            ->willReturnArgument(0);
        $this->controller->execute();
    }

    /**
     * @return array
     */
    public function emptyParamDataProvider()
    {
        return [
            ['', '0', '', ''],
            ['localhost', '1', '', ''],
            ['localhost', '1', 'user', ''],
            ['localhost', '1', '', 'password'],
        ];
    }

    /**
     * @return void
     */
    public function testExecute()
    {
        $expected = [
            'success' => true,
            'errorMessage' => '',
        ];
        $params = [
            'hostname' => 'localhost',
            'port' => '9200',
            'enableAuth' => '1',
            'username' => 'user',
            'password' => 'pass',
            'timeout' => 0,
        ];
        $this->clientHelper->expects($this->once())
            ->method('prepareClientOptions')
            ->with($params)
            ->willReturnArgument(0);
        $this->client->expects($this->once())
            ->method('ping')
            ->willReturn(['status' => 'OK']);
        $this->requestMock->expects($this->once())
            ->method('getParams')
            ->willReturn($params);
        $this->resultJson->expects($this->once())->method('setData')->with($expected);
        $this->controller->execute();
    }

    /**
     * @return void
     */
    public function testExecuteFailedPing()
    {
        $expected = [
            'success' => false,
            'errorMessage' => '',
        ];
        $params = [
            'hostname' => 'localhost',
            'port' => '9201',
            'enableAuth' => '1',
            'username' => 'user',
            'password' => 'pass',
            'timeout' => 0,
        ];
        $this->clientHelper->expects($this->once())
            ->method('prepareClientOptions')
            ->with($params)
            ->willReturnArgument(0);
        $this->client->expects($this->once())
            ->method('ping')
            ->willReturn(['status' => 'ERROR']);
        $this->requestMock->expects($this->once())
            ->method('getParams')
            ->willReturn($params);
        $this->resultJson->expects($this->once())->method('setData')->with($expected);
        $this->controller->execute();
    }

    /**
     * @return void
     */
    public function testExecuteException()
    {
        $expected = [
            'success' => false,
            'errorMessage' => __('Something went wrong'),
        ];
        $params = [
            'hostname' => 'localhost',
            'port' => '9200',
            'enableAuth' => '1',
            'username' => 'user',
            'password' => 'pass',
            'timeout' => 0,
        ];
        $this->clientHelper->expects($this->once())
            ->method('prepareClientOptions')
            ->with($params)
            ->willReturnArgument(0);
        $this->client->expects($this->once())
            ->method('ping')
            ->willThrowException(new \Exception('<p>Something went wrong<\p>'));
        $this->requestMock->expects($this->once())
            ->method('getParams')
            ->willReturn($params);
        $this->resultJson->expects($this->once())->method('setData')->with($expected);
        $this->controller->execute();
    }
}
