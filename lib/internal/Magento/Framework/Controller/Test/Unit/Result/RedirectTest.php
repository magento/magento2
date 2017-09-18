<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Controller\Test\Unit\Result;

use Magento\Framework\App\Response\HttpInterface as HttpResponseInterface;
use \Magento\Framework\Controller\Result\Redirect;

class RedirectTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Framework\Controller\Result\Redirect */
    protected $redirect;

    /** @var \Magento\Framework\App\Response\RedirectInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $redirectInterface;

    /** @var \Magento\Framework\UrlInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $urlBuilder;

    /** @var \Magento\Framework\UrlInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $urlInterface;

    /** @var HttpResponseInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $response;

    protected function setUp()
    {
        $this->redirectInterface = $this->createMock(\Magento\Framework\App\Response\RedirectInterface::class);
        $this->urlBuilder = $this->createMock(\Magento\Framework\UrlInterface::class);
        $this->urlInterface = $this->createMock(\Magento\Framework\UrlInterface::class);
        $this->response = $this->createMock(HttpResponseInterface::class);
        $this->redirect = new Redirect($this->redirectInterface, $this->urlInterface);
    }

    public function testSetRefererUrl()
    {
        $this->redirectInterface->expects($this->once())->method('getRefererUrl');
        $this->assertInstanceOf(
            \Magento\Framework\Controller\Result\Redirect::class,
            $this->redirect->setRefererUrl()
        );
    }

    public function testSetRefererOrBaseUrl()
    {
        $this->redirectInterface->expects($this->once())->method('getRedirectUrl');
        $this->assertInstanceOf(
            \Magento\Framework\Controller\Result\Redirect::class,
            $this->redirect->setRefererOrBaseUrl()
        );
    }

    public function testSetUrl()
    {
        $url = 'http://test.com';
        $this->assertInstanceOf(\Magento\Framework\Controller\Result\Redirect::class, $this->redirect->setUrl($url));
    }

    public function testSetPath()
    {
        $path = 'test/path';
        $params = ['one' => 1, 'two' => 2];
        $this->redirectInterface->expects($this->once())->method('updatePathParams')->with($params)->will(
            $this->returnValue($params)
        );
        $this->assertInstanceOf(
            \Magento\Framework\Controller\Result\Redirect::class,
            $this->redirect->setPath($path, $params)
        );
    }

    public function testRender()
    {
        $this->response->expects($this->once())->method('setRedirect');
        $this->assertInstanceOf(
            \Magento\Framework\Controller\Result\Redirect::class,
            $this->redirect->renderResult($this->response)
        );
    }
}
