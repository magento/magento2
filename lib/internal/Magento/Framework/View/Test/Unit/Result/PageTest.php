<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit\Result;

use Magento\Framework\View\Page\Config as PageConfig;

/**
 * Result Page Test
 */
class PageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\Result\Page
     */
    protected $page;

    /**
     * @var \Magento\Framework\View\Element\Template\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var \Magento\Framework\App\Request\Http|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    /**
     * @var \Magento\Framework\View\Layout|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $layout;

    /**
     * @var \Magento\Framework\View\Model\Layout\Merge|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $layoutMerge;

    /**
     * @var \Magento\Framework\View\Page\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $pageConfig;

    /**
     * @var \Magento\Framework\Translate\InlineInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $translateInline;

    /**
     * @var \Magento\Framework\View\Page\Config\Renderer|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $pageConfigRenderer;

    /**
     * @var \Magento\Framework\View\FileSystem|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $viewFileSystem;

    /**
     * @var \Magento\Framework\View\LayoutFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $layoutFactory;

    protected function setUp()
    {
        $this->layout = $this->getMockBuilder('Magento\Framework\View\Layout')
            ->setMethods(['addHandle', 'getUpdate', 'isLayoutDefined'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->layoutFactory = $this->getMockBuilder('Magento\Framework\View\LayoutFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->layoutFactory->expects($this->any())->method('create')->will($this->returnValue($this->layout));
        $this->layoutMerge = $this->getMockBuilder('Magento\Framework\View\Model\Layout\Merge')
            ->disableOriginalConstructor()
            ->getMock();

        $this->layout->expects($this->any())
            ->method('getUpdate')
            ->will($this->returnValue($this->layoutMerge));

        $this->request = $this->getMockBuilder('Magento\Framework\App\Request\Http')
            ->disableOriginalConstructor()
            ->getMock();

        $this->pageConfig = $this->getMockBuilder('Magento\Framework\View\Page\Config')
            ->disableOriginalConstructor()
            ->getMock();

        $this->viewFileSystem = $this->getMockBuilder('Magento\Framework\View\FileSystem')
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->context = $objectManagerHelper->getObject('Magento\Framework\View\Element\Template\Context', [
            'layout' => $this->layout,
            'request' => $this->request,
            'viewFileSystem' => $this->viewFileSystem,
            'pageConfig' => $this->pageConfig
        ]);

        $this->translateInline = $this->getMock('Magento\Framework\Translate\InlineInterface');

        $this->pageConfigRenderer = $this->getMockBuilder('Magento\Framework\View\Page\Config\Renderer')
            ->disableOriginalConstructor()
            ->getMock();

        $pageConfigRendererFactory = $this->getMockBuilder('Magento\Framework\View\Page\Config\RendererFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $pageConfigRendererFactory->expects($this->once())
            ->method('create')
            ->with(['pageConfig' => $this->pageConfig])
            ->willReturn($this->pageConfigRenderer);

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->page = $objectManagerHelper->getObject(
            'Magento\Framework\View\Result\Page',
            [
                'isIsolated' => true,
                'layoutFactory' => $this->layoutFactory,
                'context' => $this->context,
                'translateInline' => $this->translateInline,
                'pageConfigRendererFactory' => $pageConfigRendererFactory,
            ]
        );
    }

    public function testInitLayout()
    {
        $handleDefault = 'default';
        $fullActionName = 'full_action_name';
        $this->request->expects($this->any())
            ->method('getFullActionName')
            ->will($this->returnValue($fullActionName));

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
            ->will($this->returnValue($fullActionName));

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
            ->will($this->returnValue($fullActionName));

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
            ->will($this->returnValue($fullActionName));

        $this->layoutMerge->expects($this->any())
            ->method('addHandle')
            ->with($expected)
            ->willReturnSelf();

        $this->page->addPageLayoutHandles($parameters, $defaultHandle);
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
