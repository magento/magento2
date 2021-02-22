<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Test\Unit\Controller\Adminhtml\Indexer;

class ListActionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Indexer\Controller\Adminhtml\Indexer\ListAction
     */
    protected $object;

    /**
     * @var \Magento\Backend\App\Action\Context
     */
    protected $contextMock;

    /**
     * @var \Magento\Framework\View\Element\AbstractBlock
     */
    protected $block;

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $layout;

    /**
     * @var \Magento\Framework\App\ViewInterface
     */
    protected $view;

    /**
     * @var \Magento\Framework\View\Result\Page
     */
    protected $page;

    /**
     * @var \Magento\Backend\Model\Menu
     */
    protected $menu;

    /**
     * @var \Magento\Framework\View\Page\Config
     */
    protected $config;

    /**
     * @var \Magento\Backend\Model\Menu\Item
     */
    protected $items;

    /**
     * @var \Title
     */
    protected $title;

    /**
     * Set up test
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp(): void
    {
        $this->contextMock = $this->createPartialMock(\Magento\Backend\App\Action\Context::class, [
                'getAuthorization',
                'getSession',
                'getActionFlag',
                'getAuth',
                'getView',
                'getHelper',
                'getBackendUrl',
                'getFormKeyValidator',
                'getLocaleResolver',
                'getCanUseBaseUrl',
                'getRequest',
                'getResponse',
                'getObjectManager',
                'getMessageManager'
            ]);

        $this->response = $this->createPartialMock(
            \Magento\Framework\App\ResponseInterface::class,
            ['setRedirect', 'sendResponse']
        );

        $this->request = $this->getMockForAbstractClass(
            \Magento\Framework\App\RequestInterface::class,
            ['getParam', 'getRequest'],
            '',
            false
        );

        $this->view = $this->createPartialMock(\Magento\Framework\App\ViewInterface::class, [
                'loadLayout',
                'getPage',
                'getConfig',
                'getTitle',
                'loadLayoutUpdates',
                'renderLayout',
                'getDefaultLayoutHandle',
                'generateLayoutXml',
                'addPageLayoutHandles',
                'generateLayoutBlocks',
                'getLayout',
                'addActionLayoutHandles',
                'setIsLayoutLoaded',
                'isLayoutLoaded'
            ]);

        $this->block = $this->createPartialMock(
            \Magento\Framework\View\Element\AbstractBlock::class,
            ['setActive', 'getMenuModel']
        );

        $this->layout = $this->getMockForAbstractClass(
            \Magento\Framework\View\LayoutInterface::class,
            ['getBlock'],
            '',
            false
        );

        $this->menu = $this->createPartialMock(\Magento\Backend\Model\Menu::class, ['getParentItems']);

        $this->items = $this->createPartialMock(\Magento\Backend\Model\Menu\Item::class, ['getParentItems']);

        $this->contextMock->expects($this->any())->method("getRequest")->willReturn($this->request);
        $this->contextMock->expects($this->any())->method("getResponse")->willReturn($this->response);
        $this->contextMock->expects($this->any())->method('getView')->willReturn($this->view);

        $this->page = $this->createPartialMock(\Magento\Framework\View\Result\Page::class, ['getConfig']);
        $this->config = $this->createPartialMock(\Magento\Framework\View\Result\Page::class, ['getTitle']);
        $this->title = $this->getMockBuilder('Title')
            ->setMethods(['prepend'])
            ->getMock();

        $this->block->expects($this->any())->method('setActive')->willReturn(1);
        $this->view->expects($this->any())->method('getLayout')->willReturn($this->layout);
        $this->layout->expects($this->any())->method('getBlock')->with('menu')->willReturn($this->block);
        $this->block->expects($this->any())->method('getMenuModel')->willReturn($this->menu);
        $this->menu->expects($this->any())->method('getParentItems')->willReturn($this->items);

        $this->object = new \Magento\Indexer\Controller\Adminhtml\Indexer\ListAction($this->contextMock);
    }

    public function testExecute()
    {
        $this->view->expects($this->any())
            ->method('loadLayout')
            ->willReturn(1);

        $this->view->expects($this->any())
            ->method('getPage')
            ->willReturn($this->page);

        $this->page->expects($this->any())
            ->method('getConfig')
            ->willReturn($this->config);

        $this->config->expects($this->any())
            ->method('getTitle')
            ->willReturn($this->title);

        $this->title->expects($this->any())
            ->method('prepend')->with(__('Index Management'))
            ->willReturn(1);

        $this->view->expects($this->any())
            ->method('renderLayout')
            ->willReturn(1);

        $result = $this->object->execute();
        $this->assertNull($result);
    }
}
