<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Test\Unit\Controller\Adminhtml\Indexer;

class ListActionTest extends \PHPUnit_Framework_TestCase
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
    protected function setUp()
    {
        $this->contextMock = $this->getMock(
            \Magento\Backend\App\Action\Context::class,
            [
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
            ],
            [],
            '',
            false
        );

        $this->response = $this->getMock(
            \Magento\Framework\App\ResponseInterface::class,
            ['setRedirect', 'sendResponse'],
            [],
            '',
            false
        );

        $this->request = $this->getMockForAbstractClass(
            \Magento\Framework\App\RequestInterface::class,
            ['getParam', 'getRequest'],
            '',
            false
        );

        $this->view = $this->getMock(
            \Magento\Framework\App\ViewInterface::class,
            [
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
            ],
            [],
            '',
            false
        );

        $this->block = $this->getMock(
            \Magento\Framework\View\Element\AbstractBlock::class,
            ['setActive', 'getMenuModel'],
            [],
            '',
            false
        );

        $this->layout = $this->getMockForAbstractClass(
            \Magento\Framework\View\LayoutInterface::class,
            ['getBlock'],
            '',
            false
        );

        $this->menu = $this->getMock(
            \Magento\Backend\Model\Menu::class,
            ['getParentItems'],
            [],
            '',
            false
        );

        $this->items = $this->getMock(
            \Magento\Backend\Model\Menu\Item::class,
            ['getParentItems'],
            [],
            '',
            false
        );

        $this->contextMock->expects($this->any())->method("getRequest")->willReturn($this->request);
        $this->contextMock->expects($this->any())->method("getResponse")->willReturn($this->response);
        $this->contextMock->expects($this->any())->method('getView')->will($this->returnValue($this->view));

        $this->page = $this->getMock(\Magento\Framework\View\Result\Page::class, ['getConfig'], [], '', false);
        $this->config = $this->getMock(\Magento\Framework\View\Result\Page::class, ['getTitle'], [], '', false);
        $this->title = $this->getMock('Title', ['prepend'], [], '', false);

        $this->block->expects($this->any())->method('setActive')->will($this->returnValue(1));
        $this->view->expects($this->any())->method('getLayout')->will($this->returnValue($this->layout));
        $this->layout->expects($this->any())->method('getBlock')->with('menu')->will($this->returnValue($this->block));
        $this->block->expects($this->any())->method('getMenuModel')->will($this->returnValue($this->menu));
        $this->menu->expects($this->any())->method('getParentItems')->will($this->returnValue($this->items));

        $this->object = new \Magento\Indexer\Controller\Adminhtml\Indexer\ListAction($this->contextMock);
    }

    public function testExecute()
    {
        $this->view->expects($this->any())
            ->method('loadLayout')
            ->will($this->returnValue(1));

        $this->view->expects($this->any())
            ->method('getPage')
            ->will($this->returnValue($this->page));

        $this->page->expects($this->any())
            ->method('getConfig')
            ->will($this->returnValue($this->config));

        $this->config->expects($this->any())
            ->method('getTitle')
            ->will($this->returnValue($this->title));

        $this->title->expects($this->any())
            ->method('prepend')->with(__('Index Management'))
            ->will($this->returnValue(1));

        $this->view->expects($this->any())
            ->method('renderLayout')
            ->will($this->returnValue(1));

        $this->object->execute();
    }
}
