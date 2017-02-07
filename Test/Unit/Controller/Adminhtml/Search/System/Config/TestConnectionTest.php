<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdvancedSearch\Test\Unit\Controller\Adminhtml\Search\System\Config;

use Magento\AdvancedSearch\Controller\Adminhtml\Search\System\Config\TestConnection;
use Magento\AdvancedSearch\Model\Client\ClientResolver;
use Magento\AdvancedSearch\Model\Client\ClientInterface;

/**
 * Class TestConnectionTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TestConnectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\Request\Http|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var ClientResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    private $clientResolverMock;

    /**
     * @var ClientInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $clientMock;

    /**
     * @var \Magento\Framework\Controller\Result\Json|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultJson;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultJsonFactory;

    /**
     * @var \Magento\Framework\Filter\StripTags|\PHPUnit_Framework_MockObject_MockObject
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
    protected function setUp()
    {
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->requestMock = $this->getMock(\Magento\Framework\App\Request\Http::class, ['getParams'], [], '', false);
        $responseMock = $this->getMock(\Magento\Framework\App\Response\Http::class, [], [], '', false);

        $context = $this->getMock(
            \Magento\Backend\App\Action\Context::class,
            ['getRequest', 'getResponse', 'getMessageManager', 'getSession'],
            $helper->getConstructArguments(
                \Magento\Backend\App\Action\Context::class,
                [
                    'request' => $this->requestMock
                ]
            )
        );
        $context->expects($this->once())->method('getRequest')->will($this->returnValue($this->requestMock));
        $context->expects($this->once())->method('getResponse')->will($this->returnValue($responseMock));

        $this->clientResolverMock = $this->getMockBuilder(\Magento\AdvancedSearch\Model\Client\ClientResolver::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->clientMock = $this->getMock(\Magento\AdvancedSearch\Model\Client\ClientInterface::class);

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
