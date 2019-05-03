<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Email\Test\Unit\Controller\Adminhtml\Email\Template;

use Magento\Email\Controller\Adminhtml\Email\Template\Preview;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\View;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Config;
use Magento\Framework\View\Page\Title;
use Magento\Framework\View\Result\Page;

/**
 * Preview email template test.
 */
class PreviewTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Preview
     */
    private $object;

    /**
     * @var Context
     */
    private $context;

    /**
     * @var Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    private $coreRegistryMock;

    /**
     * @var View|\PHPUnit_Framework_MockObject_MockObject
     */
    private $viewMock;

    /**
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var Page|\PHPUnit_Framework_MockObject_MockObject
     */
    private $pageMock;

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $pageConfigMock;

    /**
     * @var Title|\PHPUnit_Framework_MockObject_MockObject
     */
    private $pageTitleMock;

    /**
     * @var \Magento\Framework\App\ResponseInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $responseMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->coreRegistryMock = $this->getMockBuilder(\Magento\Framework\Registry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->viewMock = $this->getMockBuilder(\Magento\Framework\App\View::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->requestMock = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->getMock();
        $this->pageMock = $this->getMockBuilder(\Magento\Framework\View\Result\Page::class)
            ->disableOriginalConstructor()
            ->setMethods(['getConfig'])
            ->getMock();
        $this->pageConfigMock = $this->getMockBuilder(\Magento\Framework\View\Page\Config::class)
            ->setMethods(['getTitle'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->pageTitleMock = $this->getMockBuilder(\Magento\Framework\View\Page\Title::class)
            ->setMethods(['prepend'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->responseMock = $this->getMockBuilder(\Magento\Framework\App\ResponseInterface::class)
            ->setMethods(['setHeader'])
            ->getMockForAbstractClass();

        $this->context = $objectManager->getObject(
            \Magento\Backend\App\Action\Context::class,
            [
                'request' => $this->requestMock,
                'view' => $this->viewMock,
                'response' => $this->responseMock,
            ]
        );
        $this->object = $objectManager->getObject(
            \Magento\Email\Controller\Adminhtml\Email\Template\Preview::class,
            [
                'context' => $this->context,
                'coreRegistry' => $this->coreRegistryMock,
            ]
        );
    }

    /**
     * @return void
     */
    public function testExecute()
    {
        $this->viewMock->expects($this->once())
            ->method('getPage')
            ->willReturn($this->pageMock);
        $this->pageMock->expects($this->once())
            ->method('getConfig')
            ->willReturn($this->pageConfigMock);
        $this->pageConfigMock->expects($this->once())
            ->method('getTitle')
            ->willReturn($this->pageTitleMock);
        $this->pageTitleMock->expects($this->once())
            ->method('prepend')
            ->willReturnSelf();
        $this->responseMock->expects($this->once())
            ->method('setHeader')
            ->with('Content-Security-Policy', "script-src 'self'");

        $this->assertNull($this->object->execute());
    }
}
