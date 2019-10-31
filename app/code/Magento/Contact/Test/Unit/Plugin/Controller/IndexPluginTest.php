<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Contact\Test\Unit\Plugin\Controller;

use Magento\Contact\Controller\Index\Index;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\Response\RedirectInterface as Redirect;
use Magento\Framework\App\ResponseInterface as Response;
use Magento\Framework\App\RequestInterface as Request;
use Magento\Contact\Plugin\Controller\IndexPlugin;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class IndexPluginTest
 *
 * @package Magento\Contact\Test\Unit\Plugin\Controller
 */
class IndexPluginTest extends TestCase
{
    const CONTACT_REQUEST_PREFIX = '/contact/';

    /** @var Index|MockObject $indexController */
    private $indexController;

    /** @var Http|MockObject $http */
    private $http;

    /** @var Redirect|MockObject $redirectInterface */
    private $redirectInterface;

    /** @var Response|MockObject $responseInterface */
    private $responseInterface;

    /** @var Request|MockObject $requestInterface */
    private $requestInterface;

    /** @var IndexPlugin $instance */
    private $instance;

    protected function setUp()
    {
        $this->indexController = $this->getMockBuilder(Index::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->http = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->redirectInterface = $this->getMockBuilder(Redirect::class)
            ->getMockForAbstractClass();

        $this->responseInterface = $this->getMockBuilder(Response::class)
            ->getMockForAbstractClass();

        $this->requestInterface = $this->getMockBuilder(Request::class)
            ->getMockForAbstractClass();

        $this->instance = new IndexPlugin($this->redirectInterface);
    }

    /**
     * Test MUST redirect if the request has "index" in the suffix
     */
    public function testBeforeExecuteMustRedirect()
    {
        $this->indexController->expects($this->once())
            ->method('getRequest')
            ->willReturn($this->http);

        $this->http->expects($this->once())
            ->method('getRequestString')
            ->willReturn(self::CONTACT_REQUEST_PREFIX . 'index');

        $this->indexController->expects($this->once())
            ->method('getResponse')
            ->willReturn($this->responseInterface);

        $this->redirectInterface->expects($this->once())
            ->method('redirect')
            ->with($this->responseInterface, 'contact');

        $return = $this->instance->beforeExecute($this->indexController);
        $this->assertEquals(null, $return);
    }

    /**
     * Test SHALL NOT redirect if the request don't has "index" in the suffix
     */
    public function testBeforeExecuteShallNotRedirect()
    {
        $this->indexController->expects($this->once())
            ->method('getRequest')
            ->willReturn($this->http);

        $this->http->expects($this->once())
            ->method('getRequestString')
            ->willReturn(self::CONTACT_REQUEST_PREFIX);

        $this->indexController->expects($this->never())
            ->method('getResponse');

        $this->redirectInterface->expects($this->never())
            ->method('redirect');

        $return = $this->instance->beforeExecute($this->indexController);
        $this->assertEquals(null, $return);
    }
}
