<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Test\Unit\Model\View\Result;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class PageTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Backend\Model\View\Result\Page
     */
    protected $resultPage;

    /**
     * @var \Magento\Framework\View\Element\Template\Context
     */
    protected $context;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Framework\View\LayoutInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $layoutMock;

    /**
     * @var \Magento\Backend\Block\Widget\Breadcrumbs|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $breadcrumbsBlockMock;

    protected function setUp(): void
    {
        $this->layoutMock = $this->getMockBuilder(\Magento\Framework\View\LayoutInterface::class)
            ->setMethods(['setGeneratorPool'])
            ->getMockForAbstractClass();
        $this->breadcrumbsBlockMock = $this->getMockBuilder(\Magento\Backend\Block\Widget\Breadcrumbs::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->context = $this->objectManagerHelper->getObject(
            \Magento\Framework\View\Element\Template\Context::class,
            ['layout' => $this->layoutMock]
        );
        $this->resultPage = $this->objectManagerHelper->getObject(
            \Magento\Backend\Model\View\Result\Page::class,
            ['context' => $this->context]
        );
    }

    public function testAddBreadcrumb()
    {
        $label = 'label';
        $title = 'title';
        $link = '/link';

        $this->layoutMock->expects($this->once())
            ->method('getBlock')
            ->with('breadcrumbs')
            ->willReturn($this->breadcrumbsBlockMock);
        $this->breadcrumbsBlockMock->expects($this->once())
            ->method('addLink')
            ->with($label, $title, $link)
            ->willReturnSelf();

        $this->assertSame($this->resultPage, $this->resultPage->addBreadcrumb($label, $title, $link));
    }

    public function testAddBreadcrumbNoBlock()
    {
        $label = 'label';
        $title = 'title';

        $this->layoutMock->expects($this->once())
            ->method('getBlock')
            ->with('breadcrumbs')
            ->willReturn(false);
        $this->breadcrumbsBlockMock->expects($this->never())
            ->method('addLink');

        $this->assertSame($this->resultPage, $this->resultPage->addBreadcrumb($label, $title));
    }
}
