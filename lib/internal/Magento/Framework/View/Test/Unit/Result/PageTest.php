<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit\Result;

use Magento\Framework\App\Request\Http;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Translate\InlineInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\EntitySpecificHandlesList;
use Magento\Framework\View\FileSystem;
use Magento\Framework\View\Layout;
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\View\Model\Layout\Merge;
use Magento\Framework\View\Page\Config;
use Magento\Framework\View\Page\Config\Renderer;
use Magento\Framework\View\Page\Config\RendererFactory;
use Magento\Framework\View\Result\Page;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Result Page Test
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PageTest extends TestCase
{
    /**
     * @var Page
     */
    private $page;

    /**
     * @var Context|MockObject
     */
    private $context;

    /**
     * @var Http|MockObject
     */
    private $request;

    /**
     * @var Layout|MockObject
     */
    private $layout;

    /**
     * @var Merge|MockObject
     */
    private $layoutMerge;

    /**
     * @var Config|MockObject
     */
    private $pageConfig;

    /**
     * @var InlineInterface|MockObject
     */
    private $translateInline;

    /**
     * @var Renderer|MockObject
     */
    private $pageConfigRenderer;

    /**
     * @var FileSystem|MockObject
     */
    private $viewFileSystem;

    /**
     * @var LayoutFactory|MockObject
     */
    private $layoutFactory;

    /**
     * @var MockObject|EntitySpecificHandlesList
     */
    private $entitySpecificHandlesListMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->layout = $this->getMockBuilder(Layout::class)
            ->onlyMethods(['getUpdate'])
            ->addMethods(['addHandle', 'isLayoutDefined'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->layoutFactory = $this->getMockBuilder(LayoutFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->layoutFactory->expects($this->any())->method('create')->willReturn($this->layout);
        $this->layoutMerge = $this->getMockBuilder(Merge::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->layout->expects($this->any())
            ->method('getUpdate')
            ->willReturn($this->layoutMerge);

        $this->request = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->pageConfig = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->viewFileSystem = $this->getMockBuilder(FileSystem::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerHelper = new ObjectManager($this);
        $this->context = $objectManagerHelper->getObject(
            Context::class,
            [
                'layout' => $this->layout,
                'request' => $this->request,
                'viewFileSystem' => $this->viewFileSystem,
                'pageConfig' => $this->pageConfig
            ]
        );

        $this->translateInline = $this->getMockForAbstractClass(InlineInterface::class);

        $this->pageConfigRenderer = $this->getMockBuilder(Renderer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $pageConfigRendererFactory = $this->getMockBuilder(RendererFactory::class)->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $pageConfigRendererFactory->expects($this->once())
            ->method('create')
            ->with(['pageConfig' => $this->pageConfig])
            ->willReturn($this->pageConfigRenderer);

        $this->entitySpecificHandlesListMock = $this->createMock(EntitySpecificHandlesList::class);

        $objectManagerHelper = new ObjectManager($this);
        $this->page = $objectManagerHelper->getObject(
            Page::class,
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

    /**
     * @return void
     */
    public function testInitLayout(): void
    {
        $handleDefault = 'default';
        $fullActionName = 'full_action_name';
        $this->request->expects($this->any())
            ->method('getFullActionName')
            ->willReturn($fullActionName);

        $this->layoutMerge
            ->method('addHandle')
            ->willReturnCallback(function ($arg) use ($handleDefault, $fullActionName) {
                if ($arg == $handleDefault || $arg == $fullActionName) {
                    return $this->layoutMerge;
                }
            });
        $this->layoutMerge
            ->method('isLayoutDefined')
            ->willReturn(false);

        $this->assertEquals($this->page, $this->page->initLayout());
    }

    /**
     * @return void
     */
    public function testInitLayoutLayoutDefined(): void
    {
        $handleDefault = 'default';
        $fullActionName = 'full_action_name';
        $this->request->expects($this->any())
            ->method('getFullActionName')
            ->willReturn($fullActionName);

        $this->layoutMerge
            ->method('addHandle')
            ->willReturnCallback(function ($arg) use ($handleDefault, $fullActionName) {
                if ($arg == $handleDefault || $arg == $fullActionName) {
                    return $this->layoutMerge;
                }
            });
        $this->layoutMerge
            ->method('removeHandle')
            ->with($handleDefault)
            ->willReturn($this->layoutMerge);
        $this->layoutMerge
            ->method('isLayoutDefined')
            ->willReturn(true);

        $this->assertEquals($this->page, $this->page->initLayout());
    }

    /**
     * @return void
     */
    public function testGetConfig(): void
    {
        $this->assertEquals($this->pageConfig, $this->page->getConfig());
    }

    /**
     * @return void
     */
    public function testGetDefaultLayoutHandle(): void
    {
        $fullActionName = 'Full_Action_Name';
        $expectedFullActionName = 'full_action_name';

        $this->request->expects($this->any())
            ->method('getFullActionName')
            ->willReturn($fullActionName);

        $this->assertEquals($expectedFullActionName, $this->page->getDefaultLayoutHandle());
    }

    /**
     * @return void
     */
    public function testAddPageLayoutHandles(): void
    {
        $fullActionName = 'Full_Action_Name';
        $defaultHandle = null;
        $parameters = [
            'key_one' => 'val_one',
            'key_two' => 'val_two'
        ];
        $expected = [
            'full_action_name',
            'full_action_name_key_one_val_one',
            'full_action_name_key_two_val_two'
        ];
        $this->request->expects($this->any())
            ->method('getFullActionName')
            ->willReturn($fullActionName);

        $this->layoutMerge->expects($this->any())
            ->method('addHandle')
            ->with($expected)
            ->willReturnSelf();

        $this->entitySpecificHandlesListMock
            ->method('addHandle')
            ->willReturnCallback(
                function ($arg) {
                    if ($arg == 'full_action_name_key_one_val_one' || $arg == 'full_action_name_key_two_val_two') {
                        return null;
                    }
                }
            );

        $this->page->addPageLayoutHandles($parameters, $defaultHandle);
    }

    /**
     * @return void
     */
    public function testAddPageLayoutHandlesNotEntitySpecific(): void
    {
        $fullActionName = 'Full_Action_Name';
        $defaultHandle = null;
        $parameters = [
            'key_one' => 'val_one',
            'key_two' => 'val_two'
        ];
        $expected = [
            'full_action_name',
            'full_action_name_key_one_val_one',
            'full_action_name_key_two_val_two'
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

    /**
     * @return void
     */
    public function testAddPageLayoutHandlesWithDefaultHandle(): void
    {
        $defaultHandle = 'default_handle';
        $parameters = [
            'key_one' => 'val_one',
            'key_two' => 'val_two'
        ];
        $expected = [
            'default_handle',
            'default_handle_key_one_val_one',
            'default_handle_key_two_val_two'
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
