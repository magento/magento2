<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\Controller\Adminhtml\Design\Config;

use Magento\Theme\Controller\Adminhtml\Design\Config\Index;

class IndexTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Index
     */
    protected $controller;

    /**
     * @var \Magento\Backend\App\Action\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var \Magento\Backend\Model\View\Result\Page|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultPage;

    protected function setUp()
    {
        $this->context = $this->getMockBuilder(\Magento\Backend\App\Action\Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $resultPageFactory = $this->initResultPage();

        $this->controller = new Index($this->context, $resultPageFactory);
    }

    /**
     * @return \Magento\Framework\View\Result\PageFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function initResultPage()
    {
        $this->resultPage = $this->getMockBuilder(\Magento\Backend\Model\View\Result\Page::class)
            ->disableOriginalConstructor()
            ->getMock();

        $resultPageFactory = $this->getMockBuilder(\Magento\Framework\View\Result\PageFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resultPageFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->resultPage);
        return $resultPageFactory;
    }

    public function testExecute()
    {
        $pageTitle = $this->getMockBuilder(\Magento\Framework\View\Page\Title::class)
            ->disableOriginalConstructor()
            ->getMock();
        $pageTitle->expects($this->once())
            ->method('prepend')
            ->with(__('Design Configuration'))
            ->willReturnSelf();

        $pageConfig = $this->getMockBuilder(\Magento\Framework\View\Page\Config::class)
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
