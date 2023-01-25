<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Controller\Adminhtml\Design\Config;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\View\Page\Config;
use Magento\Framework\View\Page\Title;
use Magento\Framework\View\Result\PageFactory;
use Magento\Theme\Controller\Adminhtml\Design\Config\Index;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class IndexTest extends TestCase
{
    /**
     * @var Index
     */
    protected $controller;

    /**
     * @var Context|MockObject
     */
    protected $context;

    /**
     * @var Page|MockObject
     */
    protected $resultPage;

    protected function setUp(): void
    {
        $this->context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $resultPageFactory = $this->initResultPage();

        $this->controller = new Index($this->context, $resultPageFactory);
    }

    /**
     * @return PageFactory|MockObject
     */
    protected function initResultPage()
    {
        $this->resultPage = $this->getMockBuilder(Page::class)
            ->disableOriginalConstructor()
            ->getMock();

        $resultPageFactory = $this->getMockBuilder(PageFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resultPageFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->resultPage);
        return $resultPageFactory;
    }

    public function testExecute()
    {
        $pageTitle = $this->getMockBuilder(Title::class)
            ->disableOriginalConstructor()
            ->getMock();
        $pageTitle->expects($this->once())
            ->method('prepend')
            ->with(__('Design Configuration'))
            ->willReturnSelf();

        $pageConfig = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $pageConfig->expects($this->once())
            ->method('getTitle')
            ->willReturn($pageTitle);

        $this->resultPage->expects($this->once())
            ->method('setActiveMenu')
            ->with('Magento_Theme::design_config')
            ->willReturnSelf();
        $this->resultPage->expects($this->once())
            ->method('getConfig')
            ->willReturn($pageConfig);

        $this->assertSame($this->resultPage, $this->controller->execute());
    }
}
