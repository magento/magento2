<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Controller\Test\Unit\Result;

use \PHPUnit\Framework\TestCase;
use \Magento\Framework\App\Response\HttpInterface as HttpResponseInterface;
use \Magento\Framework\App\Response\RedirectInterface;
use \Magento\Framework\Controller\Result\Redirect;
use \Magento\Framework\UrlInterface;

class RedirectTest extends TestCase
{
    /** @var \Magento\Framework\Controller\Result\Redirect */
    protected $redirect;

    /** @var \Magento\Framework\App\Response\RedirectInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $redirectInterface;

    /** @var \Magento\Framework\UrlInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $urlBuilder;

    /** @var \Magento\Framework\UrlInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $urlInterface;

    /** @var HttpResponseInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $response;

    protected function setUp(): void
    {
        $this->redirectInterface = $this->getMockForAbstractClass(RedirectInterface::class);
        $this->urlBuilder = $this->getMockForAbstractClass(UrlInterface::class);
        $this->urlInterface = $this->getMockForAbstractClass(UrlInterface::class);
        $this->response = $this->getMockForAbstractClass(HttpResponseInterface::class);
        $this->redirect = new Redirect($this->redirectInterface, $this->urlInterface);
    }

    public function testSetRefererUrl()
    {
        $this->redirectInterface->expects($this->once())->method('getRefererUrl');
        $this->assertInstanceOf(
            Redirect::class,
            $this->redirect->setRefererUrl()
        );
    }

    public function testSetRefererOrBaseUrl()
    {
        $this->redirectInterface->expects($this->once())->method('getRedirectUrl');
        $this->assertInstanceOf(
            Redirect::class,
            $this->redirect->setRefererOrBaseUrl()
        );
    }

    public function testSetUrl()
    {
        $url = 'http://test.com';
        $this->assertInstanceOf(Redirect::class, $this->redirect->setUrl($url));
    }

    public function testSetPath()
    {
        $path = 'test/path';
        $params = ['one' => 1, 'two' => 2];
        $this->redirectInterface->expects($this->once())->method('updatePathParams')->with($params)->willReturn(
            $params
        );
        $this->assertInstanceOf(
            Redirect::class,
            $this->redirect->setPath($path, $params)
        );
    }

    /**
     * @return array
     */
    public function httpRedirectResponseStatusCodes()
    {
        return [
            [302, null],
            [302, 302],
            [303, 303]
        ];
    }

    /**
     * @param int $expectedStatusCode
     * @param int|null $actualStatusCode
     * @dataProvider httpRedirectResponseStatusCodes
     */
    public function testRender($expectedStatusCode, $actualStatusCode)
    {
        $url = 'http://test.com';
        $this->redirect->setUrl($url);
        $this->redirect->setHttpResponseCode($actualStatusCode);

        $this->response
            ->expects($this->once())
            ->method('setRedirect')
            ->with($url, $expectedStatusCode);

        $this->redirect->renderResult($this->response);
    }
}
