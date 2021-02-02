<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit\Result;

use Magento\Framework\View\Page\Config as PageConfig;
use Magento\Framework\View\EntitySpecificHandlesList;

/**
 * Result Page Test
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PageTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\View\Result\Page
     */
    private $page;

    /**
     * @var \Magento\Framework\View\Element\Template\Context|\PHPUnit\Framework\MockObject\MockObject
     */
    private $context;

    /**
     * @var \Magento\Framework\App\Request\Http|\PHPUnit\Framework\MockObject\MockObject
     */
    private $request;

    /**
     * @var \Magento\Framework\View\Layout|\PHPUnit\Framework\MockObject\MockObject
     */
    private $layout;

    /**
     * @var \Magento\Framework\View\Model\Layout\Merge|\PHPUnit\Framework\MockObject\MockObject
     */
    private $layoutMerge;

    /**
     * @var \Magento\Framework\View\Page\Config|\PHPUnit\Framework\MockObject\MockObject
     */
    private $pageConfig;

    /**
     * @var \Magento\Framework\Translate\InlineInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $translateInline;

    /**
     * @var \Magento\Framework\View\Page\Config\Renderer|\PHPUnit\Framework\MockObject\MockObject
     */
    private $pageConfigRenderer;

    /**
     * @var \Magento\Framework\View\FileSystem|\PHPUnit\Framework\MockObject\MockObject
     */
    private $viewFileSystem;

    /**
     * @var \Magento\Framework\View\LayoutFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $layoutFactory;

    /** @var \PHPUnit\Framework\MockObject\MockObject|EntitySpecificHandlesList */
    private $entitySpecificHandlesListMock;

    protected function setUp(): void
    {
        $this->layout = $this->getMockBuilder(\Magento\Framework\View\Layout::class)
            ->setMethods(['addHandle', 'getUpdate', 'isLayoutDefined'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->layoutFactory = $this->getMockBuilder(\Magento\Framework\View\LayoutFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->layoutFactory->expects($this->any())->method('create')->willReturn($this->layout);
        $this->layoutMerge = $this->getMockBuilder(\Magento\Framework\View\Model\Layout\Merge::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->layout->expects($this->any())
            ->method('getUpdate')
            ->willReturn($this->layoutMerge);

        $this->request = $this->getMockBuilder(\Magento\Framework\App\Request\Http::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->pageConfig = $this->getMockBuilder(\Magento\Framework\View\Page\Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->viewFileSystem = $this->getMockBuilder(\Magento\Framework\View\FileSystem::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->context = $objectManagerHelper->getObject(
            \Magento\Framework\View\Element\Template\Context::class,
            [
                'layout' => $this->layout,
                'request' => $this->request,
                'viewFileSystem' => $this->viewFileSystem,
                'pageConfig' => $this->pageConfig
            ]
        );

        $this->translateInline = $this->createMock(\Magento\Framework\Translate\InlineInterface::class);

        $this->pageConfigRenderer = $this->getMockBuilder(\Magento\Framework\View\Page\Config\Renderer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $pageConfigRendererFactory = $this->getMockBuilder(\Magento\Framework\View\Page\Config\RendererFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $pageConfigRendererFactory->expects($this->once())
            ->method('create')
            ->with(['pageConfig' => $this->pageConfig])
            ->willReturn($this->pageConfigRenderer);

        $this->entitySpecificHandlesListMock = $this->createMock(EntitySpecificHandlesList::class);

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->page = $objectManagerHelper->getObject(
            \Magento\Framework\View\Result\Page::class,
            [
                'isIsolated' => true,
                'layoutFactory' => $this->layoutFactory,
                'context' => $this->context,
                'translateInline' => $this->translateInline,
                'pageConfigRendererFactory' => $pageConfigRendererFactory,
                'entitySpecificHandlesList' => $this->entitySpecificHandlesListMock
            ]
        );
    }

    public function testInitLayout()
    {
        $handleDefault = 'default';
        $fullActionName = 'full_action_name';
        $this->request->expects($this->any())
            ->method('getFullActionName')
            ->willReturn($fullActionName);

        $this->layoutMerge->expects($this->at(0))
            ->method('addHandle')
            ->with($handleDefault)
            ->willReturnSelf();
        $this->layoutMerge->expects($this->at(1))
            ->method('addHandle')
            ->with($fullActionName)
            ->willReturnSelf();
        $this->layoutMerge->expects($this->at(2))
            ->method('isLayoutDefined')
            ->willReturn(false);

        $this->assertEquals($this->page, $this->page->initLayout());
    }

    public function testInitLayoutLayoutDefined()
    {
        $handleDefault = 'default';
        $fullActionName = 'full_action_name';
        $this->request->expects($this->any())
            ->method('getFullActionName')
            ->willReturn($fullActionName);

        $this->layoutMerge->expects($this->at(0))
            ->method('addHandle')
            ->with($handleDefault)
            ->willReturnSelf();
        $this->layoutMerge->expects($this->at(1))
            ->method('addHandle')
            ->with($fullActionName)
            ->willReturnSelf();
        $this->layoutMerge->expects($this->at(2))
            ->method('isLayoutDefined')
            ->willReturn(true);
        $this->layoutMerge->expects($this->at(3))
            ->method('removeHandle')
            ->with($handleDefault)
            ->willReturnSelf();

        $this->assertEquals($this->page, $this->page->initLayout());
    }

    public function testGetConfig()
    {
        $this->assertEquals($this->pageConfig, $this->page->getConfig());
    }

    public function testGetDefaultLayoutHandle()
    {
        $fullActionName = 'Full_Action_Name';
        $expectedFullActionName = 'full_action_name';

        $this->request->expects($this->any())
            ->method('getFullActionName')
            ->willReturn($fullActionName);

        $this->assertEquals($expectedFullActionName, $this->page->getDefaultLayoutHandle());
    }

    public function testAddPageLayoutHandles()
    {
        $fullActionName = 'Full_Action_Name';
        $defaultHandle = null;
        $parameters = [
            'key_one' => 'val_one',
            'key_two' => 'val_two',
        ];
        $expected = [
            'full_action_name',
            'full_action_name_key_one_val_one',
            'full_action_name_key_two_val_two',
        ];
        $this->request->expects($this->any())
            ->method('getFullActionName')
            ->willReturn($fullActionName);

        $this->layoutMerge->expects($this->any())
            ->method('addHandle')
            ->with($expected)
            ->willReturnSelf();

        $this->entitySpecificHandlesListMock->expects($this->at(0))
            ->method('addHandle')->with('full_action_name_key_one_val_one');
        $this->entitySpecificHandlesListMock->expects($this->at(1))
            ->method('addHandle')->with('full_action_name_key_two_val_two');

        $this->page->addPageLayoutHandles($parameters, $defaultHandle);
    }

    public function testAddPageLayoutHandlesNotEntitySpecific()
    {
        $fullActionName = 'Full_Action_Name';
        $defaultHandle = null;
        $parameters = [
            'key_one' => 'val_one',
            'key_two' => 'val_two',
        ];
        $expected = [
            'full_action_name',
            'full_action_name_key_one_val_one',
            'full_action_name_key_two_val_two',
        ];
        $this->request->expects($this->any())
            ->method('getFullActionName')
            ->willReturn($fullActionName);

        $this->layoutMerge->expects($this->any())
            ->method('addHandle')
            ->with($expected)
            ->willReturnSelf();

        $this->entitySpecificHandlesListMock->expects($this->never())->method('addHandle');

        $this->page->addPageLayoutHandles($parameters, $defaultHandle, false);
    }

    public function testAddPageLayoutHandlesWithDefaultHandle()
    {
        $defaultHandle = 'default_handle';
        $parameters = [
            'key_one' => 'val_one',
            'key_two' => 'val_two',
        ];
        $expected = [
            'default_handle',
            'default_handle_key_one_val_one',
            'default_handle_key_two_val_two',
        ];
        $this->request->expects($this->never())
            ->method('getFullActionName');

        $this->layoutMerge->expects($this->any())
            ->method('addHandle')
            ->with($expected)
            ->willReturnSelf();

        $this->page->addPageLayoutHandles($parameters, $defaultHandle);
    }
}
