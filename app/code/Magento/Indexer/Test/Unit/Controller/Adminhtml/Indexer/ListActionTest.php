<?php declare(strict_types=1);
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Test\Unit\Controller\Adminhtml\Indexer;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Menu;
use Magento\Backend\Model\Menu\Item;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\ViewInterface;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\View\Page\Config;
use Magento\Framework\View\Result\Page;
use Magento\Indexer\Controller\Adminhtml\Indexer\ListAction;
use PHPUnit\Framework\TestCase;

class ListActionTest extends TestCase
{
    /**
     * @var ListAction
     */
    protected $object;

    /**
     * @var Context
     */
    protected $contextMock;

    /**
     * @var AbstractBlock
     */
    protected $block;

    /**
     * @var LayoutInterface
     */
    protected $layout;

    /**
     * @var ViewInterface
     */
    protected $view;

    /**
     * @var Page
     */
    protected $page;

    /**
     * @var Menu
     */
    protected $menu;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Item
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
        $this->contextMock = $this->createPartialMock(Context::class, [
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
            ResponseInterface::class,
            ['setRedirect', 'sendResponse']
        );

        $this->request = $this->getMockForAbstractClass(
            RequestInterface::class,
            ['getParam', 'getRequest'],
            '',
            false
        );

        $this->view = $this->createPartialMock(ViewInterface::class, [
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
            AbstractBlock::class,
            ['setActive', 'getMenuModel']
        );

        $this->layout = $this->getMockForAbstractClass(
            LayoutInterface::class,
            ['getBlock'],
            '',
            false
        );

        $this->menu = $this->createPartialMock(Menu::class, ['getParentItems']);

        $this->items = $this->createPartialMock(Item::class, ['getParentItems']);

        $this->contextMock->expects($this->any())->method("getRequest")->willReturn($this->request);
        $this->contextMock->expects($this->any())->method("getResponse")->willReturn($this->response);
        $this->contextMock->expects($this->any())->method('getView')->will($this->returnValue($this->view));

        $this->page = $this->createPartialMock(Page::class, ['getConfig']);
        $this->config = $this->createPartialMock(Page::class, ['getTitle']);
        $this->title = $this->getMockBuilder('Title')
            ->setMethods(['prepend'])
            ->getMock();

        $this->block->expects($this->any())->method('setActive')->will($this->returnValue(1));
        $this->view->expects($this->any())->method('getLayout')->will($this->returnValue($this->layout));
        $this->layout->expects($this->any())->method('getBlock')->with('menu')->will($this->returnValue($this->block));
        $this->block->expects($this->any())->method('getMenuModel')->will($this->returnValue($this->menu));
        $this->menu->expects($this->any())->method('getParentItems')->will($this->returnValue($this->items));

        $this->object = new ListAction($this->contextMock);
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

        $result = $this->object->execute();
        $this->assertNull($result);
    }
}
