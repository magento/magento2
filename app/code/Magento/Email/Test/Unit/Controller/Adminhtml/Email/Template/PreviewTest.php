<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Email\Test\Unit\Controller\Adminhtml\Email\Template;

use Magento\Backend\App\Action\Context;
use Magento\Email\Controller\Adminhtml\Email\Template\Preview;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\View;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Config;
use Magento\Framework\View\Page\Config as PageConfig;
use Magento\Framework\View\Page\Title;
use Magento\Framework\View\Result\Page;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PreviewTest extends TestCase
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
     * @var View|MockObject
     */
    protected $viewMock;

    /**
     * @var RequestInterface|MockObject
     */
    protected $requestMock;

    /**
     * @var Page|MockObject
     */
    protected $pageMock;

    /**
     * @var Config|MockObject
     */
    protected $pageConfigMock;

    /**
     * @var Title|MockObject
     */
    protected $pageTitleMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->coreRegistryMock = $this->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->viewMock = $this->getMockBuilder(View::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->getMock();
        $this->pageMock = $this->getMockBuilder(Page::class)
            ->disableOriginalConstructor()
            ->setMethods(['getConfig'])
            ->getMock();
        $this->pageConfigMock = $this->getMockBuilder(PageConfig::class)
            ->setMethods(['getTitle'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->pageTitleMock = $this->getMockBuilder(Title::class)
            ->setMethods(['prepend'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->context = $objectManager->getObject(
            Context::class,
            [
                'request' => $this->requestMock,
                'view' => $this->viewMock
            ]
        );
        $this->object = $objectManager->getObject(
            Preview::class,
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
