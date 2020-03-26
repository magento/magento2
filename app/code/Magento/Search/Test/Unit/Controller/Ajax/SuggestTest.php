<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Search\Test\Unit\Controller\Ajax;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json as ResultJson;
use Magento\Framework\Controller\Result\JsonFactory as ResultJsonFactory;
use Magento\Framework\Controller\Result\Redirect as ResultRedirect;
use Magento\Framework\Controller\Result\RedirectFactory as ResultRedirectFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\Search\Controller\Ajax\Suggest;
use Magento\Search\Model\AutocompleteInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SuggestTest extends TestCase
{
    private const BASE_URL = 'https://example.test';

    /**
     * @var Suggest
     */
    private $action;

    /**
     * @var RequestInterface|MockObject
     */
    private $requestMock;

    /**
     * @var ResultJsonFactory|MockObject
     */
    private $resultJsonFactoryMock;

    /**
     * @var ResultRedirectFactory|MockObject
     */
    private $resultRedirectFactoryMock;

    /**
     * @var UrlInterface|MockObject
     */
    private $urlMock;

    /**
     * @var AutocompleteInterface|MockObject
     */
    private $autocompleteMock;

    protected function setUp()
    {
        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->setMethods(['getParam'])
            ->getMockForAbstractClass();

        $this->resultJsonFactoryMock = $this->getMockBuilder(ResultJsonFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultRedirectFactoryMock = $this->getMockBuilder(ResultRedirectFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->urlMock = $this->getMockBuilder(UrlInterface::class)
            ->setMethods(['getBaseUrl'])
            ->getMockForAbstractClass();

        $this->autocompleteMock = $this->getMockBuilder(AutocompleteInterface::class)
            ->setMethods(['getItems'])
            ->getMockForAbstractClass();

        $objectManager = new ObjectManager($this);
        $this->action = $objectManager->getObject(
            Suggest::class,
            [
                'request' => $this->requestMock,
                'resultJsonFactory' => $this->resultJsonFactoryMock,
                'resultRedirectFactory' => $this->resultRedirectFactoryMock,
                'url' => $this->urlMock,
                'autocomplete' => $this->autocompleteMock
            ]
        );
    }

    public function testResult()
    {
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('q')
            ->willReturn('QUERY');
        $this->autocompleteMock->expects($this->once())
            ->method('getItems')
            ->willReturn([]);
        $resultJsonMock = $this->getMockBuilder(ResultJson::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resultJsonMock->expects($this->once())
            ->method('setData')
            ->with([]);
        $this->resultJsonFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultJsonMock);

        $this->action->execute();
    }

    public function testResultWithoutQuery()
    {
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('q')
            ->willReturn(null);
        $resultRedirectMock = $this->getMockBuilder(ResultRedirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultRedirectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultRedirectMock);
        $this->urlMock->expects($this->once())
            ->method('getBaseUrl')
            ->willReturn(static::BASE_URL);

        $this->action->execute();
    }
}
