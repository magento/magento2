<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Authorizenet\Test\Unit\Controller\Directpost\Payment;

use Magento\Authorizenet\Controller\Directpost\Payment\Redirect;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ViewInterface;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Payment\Block\Transparent\Iframe;
use PHPUnit\Framework\MockObject\MockObject as MockObject;

/**
 * Test for Redirect
 */
class RedirectTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var RequestInterface|MockObject
     */
    private $request;

    /**
     * @var ViewInterface|MockObject
     */
    private $view;

    /**
     * @var Registry|MockObject
     */
    private $coreRegistry;

    /**
     * @var Redirect
     */
    private $controller;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->request = static::getMockForAbstractClass(RequestInterface::class);

        $this->view = static::getMockForAbstractClass(ViewInterface::class);

        $this->coreRegistry = static::getMockBuilder(Registry::class)
            ->disableOriginalConstructor()
            ->setMethods(['register'])
            ->getMock();

        $this->controller = $objectManager->getObject(Redirect::class, [
            'request' => $this->request,
            'view' => $this->view,
            'coreRegistry' => $this->coreRegistry
        ]);
    }

    /**
     * @covers \Magento\Authorizenet\Controller\Directpost\Payment\Redirect::execute
     */
    public function testExecute()
    {
        $url = 'http://test.com/redirect?=test';
        $params = [
            'order_success' => $url
        ];
        $this->request->expects(static::once())
            ->method('getParams')
            ->willReturn($params);

        $this->coreRegistry->expects(static::once())
            ->method('register')
            ->with(Iframe::REGISTRY_KEY, []);

        $this->view->expects(static::once())
            ->method('addPageLayoutHandles');
        $this->view->expects(static::once())
            ->method('loadLayout')
            ->with(false)
            ->willReturnSelf();
        $this->view->expects(static::once())
            ->method('renderLayout');

        $this->controller->execute();
    }
}
