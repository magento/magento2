<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit;

use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Response\Http;
use Magento\Framework\App\View;
use Magento\Framework\Config\ScopeInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Layout;
use Magento\Framework\View\Model\Layout\Merge;
use Magento\Framework\View\Page\Config;
use Magento\Framework\View\Page\Config\RendererFactory;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ViewTest extends TestCase
{
    /**
     * @var View
     */
    protected $_view;

    /**
     * @var MockObject
     */
    protected $_layoutMock;

    /**
     * @var MockObject
     */
    protected $_configScopeMock;

    /**
     * @var MockObject
     */
    protected $_requestMock;

    /**
     * @var MockObject
     */
    protected $_layoutProcessor;

    /**
     * @var MockObject
     */
    protected $_actionFlagMock;

    /**
     * @var MockObject
     */
    protected $_eventManagerMock;

    /**
     * @var Page|MockObject
     */
    protected $resultPage;

    /**
     * @var Http|MockObject
     */
    protected $response;

    protected function setUp(): void
    {
        $helper = new ObjectManager($this);
        $this->_layoutMock = $this->createMock(Layout::class);
        $this->_requestMock = $this->createMock(\Magento\Framework\App\Request\Http::class);
        $this->_configScopeMock = $this->getMockForAbstractClass(ScopeInterface::class);
        $this->_layoutProcessor = $this->createMock(Merge::class);
        $this->_layoutMock->expects($this->any())->method('getUpdate')
            ->willReturn($this->_layoutProcessor);
        $this->_actionFlagMock = $this->createMock(ActionFlag::class);
        $this->_eventManagerMock = $this->getMockForAbstractClass(ManagerInterface::class);
        $pageConfigMock = $this->getMockBuilder(
            Config::class
        )->disableOriginalConstructor()
            ->getMock();
        $pageConfigMock->expects($this->any())
            ->method('publicBuild')
            ->willReturnSelf();

        $pageConfigRendererFactory = $this->getMockBuilder(RendererFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->resultPage = $this->getMockBuilder(Page::class)
            ->setConstructorArgs(
                $helper->getConstructArguments(Page::class, [
                    'request' => $this->_requestMock,
                    'pageConfigRendererFactory' => $pageConfigRendererFactory,
                    'layout' => $this->_layoutMock
                ])
            )
            ->setMethods(['renderResult', 'getConfig'])
            ->getMock();
        $this->resultPage->expects($this->any())
            ->method('getConfig')
            ->willReturn($pageConfigMock);
        $pageFactory = $this->getMockBuilder(PageFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $pageFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->resultPage);

        $this->response = $this->createMock(Http::class);

        $this->_view = $helper->getObject(
            View::class,
            [
                'layout' => $this->_layoutMock,
                'request' => $this->_requestMock,
                'response' => $this->response,
                'configScope' => $this->_configScopeMock,
                'eventManager' => $this->_eventManagerMock,
                'actionFlag' => $this->_actionFlagMock,
                'pageFactory' => $pageFactory
            ]
        );
    }

    public function testGetLayout()
    {
        $this->assertEquals($this->_layoutMock, $this->_view->getLayout());
    }

    public function testLoadLayoutWhenLayoutAlreadyLoaded()
    {
        $this->expectException('RuntimeException');
        $this->expectExceptionMessage('Layout must be loaded only once.');
        $this->_view->setIsLayoutLoaded(true);
        $this->_view->loadLayout();
    }

    public function testLoadLayoutWithDefaultSetup()
    {
        $this->_layoutProcessor->expects($this->at(0))->method('addHandle')->with('default');
        $this->_requestMock->expects(
            $this->any()
        )->method(
            'getFullActionName'
        )->willReturn(
            'action_name'
        );
        $this->_view->loadLayout();
    }

    public function testLoadLayoutWhenBlocksNotGenerated()
    {
        $this->_view->loadLayout('', false, true);
    }

    public function testLoadLayoutWhenXmlNotGenerated()
    {
        $this->_view->loadLayout('', true, false);
    }

    public function testGetDefaultLayoutHandle()
    {
        $this->_requestMock->expects($this->once())
            ->method('getFullActionName')
            ->willReturn('ExpectedValue');

        $this->assertEquals('expectedvalue', $this->_view->getDefaultLayoutHandle());
    }

    public function testAddActionLayoutHandlesWhenPageLayoutHandlesExist()
    {
        $this->_requestMock->expects($this->once())
            ->method('getFullActionName')
            ->willReturn('Full_Action_Name');

        $this->_layoutProcessor->expects($this->once())
            ->method('addHandle')
            ->with('full_action_name');

        $this->_view->addActionLayoutHandles();
    }

    public function testAddPageLayoutHandles()
    {
        $pageHandles = ['full_action_name', 'full_action_name_key_value'];
        $this->_requestMock->expects($this->once())
            ->method('getFullActionName')
            ->willReturn('Full_Action_Name');

        $this->_layoutProcessor->expects($this->once())
            ->method('addHandle')
            ->with($pageHandles);
        $this->_view->addPageLayoutHandles(['key' => 'value']);
    }

    public function testGenerateLayoutBlocksWhenFlagIsNotSet()
    {
        $valueMap = [
            ['', ActionInterface::FLAG_NO_DISPATCH_BLOCK_EVENT, false],
            ['', ActionInterface::FLAG_NO_DISPATCH_BLOCK_EVENT, false],
        ];
        $this->_actionFlagMock->expects($this->any())->method('get')->willReturnMap($valueMap);
        $this->_view->generateLayoutBlocks();
    }

    public function testGenerateLayoutBlocksWhenFlagIsSet()
    {
        $valueMap = [
            ['', ActionInterface::FLAG_NO_DISPATCH_BLOCK_EVENT, true],
            ['', ActionInterface::FLAG_NO_DISPATCH_BLOCK_EVENT, true],
        ];
        $this->_actionFlagMock->expects($this->any())->method('get')->willReturnMap($valueMap);

        $this->_eventManagerMock->expects($this->never())->method('dispatch');
        $this->_view->generateLayoutBlocks();
    }

    public function testRenderLayoutIfActionFlagExist()
    {
        $this->_actionFlagMock->expects(
            $this->once()
        )->method(
            'get'
        )->with(
            '',
            'no-renderLayout'
        )->willReturn(
            true
        );
        $this->_eventManagerMock->expects($this->never())->method('dispatch');
        $this->_view->renderLayout();
    }

    public function testRenderLayoutWhenOutputNotEmpty()
    {
        $this->_actionFlagMock->expects($this->once())
            ->method('get')
            ->with('', 'no-renderLayout')
            ->willReturn(false);
        $this->_layoutMock->expects($this->once())->method('addOutputElement')->with('output');
        $this->resultPage->expects($this->once())->method('renderResult')->with($this->response);
        $this->_view->renderLayout('output');
    }

    public function testRenderLayoutWhenOutputEmpty()
    {
        $this->_actionFlagMock->expects($this->once())
            ->method('get')
            ->with('', 'no-renderLayout')
            ->willReturn(false);

        $this->_layoutMock->expects($this->never())->method('addOutputElement');
        $this->resultPage->expects($this->once())->method('renderResult')->with($this->response);
        $this->_view->renderLayout();
    }
}
