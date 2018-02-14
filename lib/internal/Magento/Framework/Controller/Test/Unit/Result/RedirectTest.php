<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Controller\Test\Unit\Result;

use \Magento\Framework\Controller\Result\Redirect;

class RedirectTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\Controller\Result\Redirect */
    protected $redirect;

    /** @var \Magento\Framework\App\Response\RedirectInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $redirectInterface;

    /** @var \Magento\Framework\UrlInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $urlBuilder;

    /** @var \Magento\Framework\UrlInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $urlInterface;

    /** @var \Magento\Framework\App\ResponseInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $response;

    protected function setUp()
    {
        $this->redirectInterface = $this->getMock(
            'Magento\Framework\App\Response\RedirectInterface',
            [],
            [],
            '',
            false
        );
        $this->urlBuilder = $this->getMock(
            'Magento\Framework\UrlInterface',
            [],
            [],
            '',
            false
        );
        $this->urlInterface = $this->getMock(
            'Magento\Framework\UrlInterface',
            [],
            [],
            '',
            false
        );
        $this->response = $this->getMock(
            'Magento\Framework\App\ResponseInterface',
            ['setRedirect', 'sendResponse'],
            [],
            '',
            false
        );
        $this->redirect = new Redirect($this->redirectInterface, $this->urlInterface);
    }

    public function testSetRefererUrl()
    {
        $this->redirectInterface->expects($this->once())->method('getRefererUrl');
        $this->assertInstanceOf('Magento\Framework\Controller\Result\Redirect', $this->redirect->setRefererUrl());
    }

    public function testSetRefererOrBaseUrl()
    {
        $this->redirectInterface->expects($this->once())->method('getRedirectUrl');
        $this->assertInstanceOf('Magento\Framework\Controller\Result\Redirect', $this->redirect->setRefererOrBaseUrl());
    }

    public function testSetUrl()
    {
        $url = 'http://test.com';
        $this->assertInstanceOf('Magento\Framework\Controller\Result\Redirect', $this->redirect->setUrl($url));
    }

    public function testSetPath()
    {
        $path = 'test/path';
        $params = ['one' => 1, 'two' => 2];
        $this->redirectInterface->expects($this->once())->method('updatePathParams')->with($params)->will(
            $this->returnValue($params)
        );
        $this->assertInstanceOf(
            'Magento\Framework\Controller\Result\Redirect',
            $this->redirect->setPath($path, $params)
        );
    }

    public function testRender()
    {
        $this->response->expects($this->once())->method('setRedirect');
        $this->assertInstanceOf(
            'Magento\Framework\Controller\Result\Redirect',
            $this->redirect->renderResult($this->response)
        );
    }
}
