<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Backend\Test\Unit\Model\View\Result;

use Magento\Backend\Block\Widget\Breadcrumbs;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\LayoutInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PageTest extends TestCase
{
    use MockCreationTrait;
    /**
     * @var Page
     */
    protected $resultPage;

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManagerHelper;

    /**
     * @var LayoutInterface|MockObject
     */
    protected $layoutMock;

    /**
     * @var Breadcrumbs|MockObject
     */
    protected $breadcrumbsBlockMock;

    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        
        $this->layoutMock = $this->createPartialMockWithReflection(
            LayoutInterface::class,
            [
                'setGeneratorPool', 'getBlock', 'getUpdate', 'generateXml', 'generateElements',
                'renderElement', 'addOutputElement', 'getOutput', 'hasElement', 'unsetElement',
                'getAllBlocks', 'getChildBlock', 'setChild', 'reorderChild', 'unsetChild',
                'getChildNames', 'getChildBlocks', 'getChildName', 'addToParentGroup',
                'getGroupChildNames', 'getParentName', 'createBlock', 'addBlock', 'addContainer',
                'renameElement', 'getElementAlias', 'removeOutputElement', 'getMessagesBlock',
                'getBlockSingleton', 'getElementProperty', 'isBlock', 'isContainer',
                'isManipulationAllowed', 'setBlock', 'isCacheable'
            ]
        );
        $this->breadcrumbsBlockMock = $this->createMock(Breadcrumbs::class);
        
        $this->context = $this->objectManagerHelper->getObject(
            Context::class,
            ['layout' => $this->layoutMock]
        );
        $this->resultPage = $this->objectManagerHelper->getObject(
            Page::class,
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
