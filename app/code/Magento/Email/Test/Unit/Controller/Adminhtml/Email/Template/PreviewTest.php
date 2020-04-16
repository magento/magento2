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
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Config;
use Magento\Framework\View\Page\Title;
use Magento\Framework\View\Result\Page;

/**
 * Preview Test.
 */
class PreviewTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Preview
     */
    protected $object;

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var View|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $viewMock;

    /**
     * @var RequestInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $requestMock;

    /**
     * @var Page|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $pageMock;

    /**
     * @var Config|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $pageConfigMock;

    /**
     * @var Title|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $pageTitleMock;

    protected function setUp(): void
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

        $this->context = $objectManager->getObject(
            \Magento\Backend\App\Action\Context::class,
            [
                'request' => $this->requestMock,
                'view' => $this->viewMock
            ]
        );
        $this->object = $objectManager->getObject(
            \Magento\Email\Controller\Adminhtml\Email\Template\Preview::class,
            [
                'context' => $this->context,
            ]
        );
    }

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

        $this->assertNull($this->object->execute());
    }
}
