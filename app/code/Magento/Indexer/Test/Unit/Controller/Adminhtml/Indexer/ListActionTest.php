<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

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

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
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

        $response = $this->getMockBuilder(ResponseInterface::class)
            ->addMethods(['setRedirect'])
            ->onlyMethods(['sendResponse'])
            ->getMockForAbstractClass();

        $request = $this->getMockForAbstractClass(
            RequestInterface::class,
            ['getParam', 'getRequest'],
            '',
            false
        );

        $this->view = $this->getMockBuilder(ViewInterface::class)
            ->addMethods(['getConfig', 'getTitle'])
            ->onlyMethods([
                'loadLayout',
                'getPage',
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
            ])
            ->getMockForAbstractClass();

        $this->block = $this->getMockBuilder(AbstractBlock::class)
            ->addMethods(['setActive', 'getMenuModel'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->layout = $this->getMockForAbstractClass(
            LayoutInterface::class,
            ['getBlock'],
            '',
            false
        );

        $this->menu = $this->createPartialMock(Menu::class, ['getParentItems']);

        $this->items = $this->getMockBuilder(Item::class)
            ->addMethods(['getParentItems'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->contextMock->expects($this->any())->method("getRequest")->willReturn($request);
        $this->contextMock->expects($this->any())->method("getResponse")->willReturn($response);
        $this->contextMock->expects($this->any())->method('getView')->willReturn($this->view);

        $this->page = $this->createPartialMock(Page::class, ['getConfig']);
        $this->config = $this->getMockBuilder(Page::class)
            ->addMethods(['getTitle'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->title = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['prepend'])
            ->getMock();

        $this->block->expects($this->any())->method('setActive')->willReturn(1);
        $this->view->expects($this->any())->method('getLayout')->willReturn($this->layout);
        $this->layout->expects($this->any())->method('getBlock')->with('menu')->willReturn($this->block);
        $this->block->expects($this->any())->method('getMenuModel')->willReturn($this->menu);
        $this->menu->expects($this->any())->method('getParentItems')->willReturn($this->items);

        $this->object = new ListAction($this->contextMock);
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
