<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CurrencySymbol\Test\Unit\Controller\Adminhtml\System\Currencysymbol;

use Magento\Backend\Model\Menu;
use Magento\Backend\Model\Menu\Item;
use Magento\CurrencySymbol\Controller\Adminhtml\System\Currencysymbol\Index;
use Magento\Framework\App\ViewInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\BlockInterface;
use Magento\Framework\View\Layout;
use Magento\Framework\View\Page\Config;
use Magento\Framework\View\Page\Title;
use Magento\Framework\View\Result\Page;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class IndexTest extends TestCase
{
    /**
     * @var Index
     */
    protected $action;

    /**
     * @var ViewInterface|MockObject
     */
    protected $viewMock;

    /**
     * @var Layout|MockObject
     */
    protected $layoutMock;

    /**
     * @var BlockInterface|MockObject
     */
    protected $blockMock;

    /**
     * @var Menu|MockObject
     */
    protected $menuMock;

    /**
     * @var Item|MockObject
     */
    protected $menuItemMock;

    /**
     * @var Page|MockObject
     */
    protected $pageMock;

    /**
     * @var Config|MockObject
     */
    protected $pageConfigMock;

    /**
     * @var Title|MockObject
     */
    protected $titleMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->menuItemMock = $this->createMock(Item::class);
        $this->menuMock = $this->createMock(Menu::class);

        $this->titleMock = $this->createMock(Title::class);

        $this->pageConfigMock = $this->createMock(Config::class);

        $this->pageMock = $this->createMock(Page::class);

        $this->blockMock = $this->getMockForAbstractClass(
            BlockInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['addLink', 'setActive', 'getMenuModel']
        );

        $this->layoutMock = $this->createMock(Layout::class);

        $this->viewMock = $this->getMockForAbstractClass(ViewInterface::class);

        $this->action = $objectManager->getObject(
            Index::class,
            [
                'view' => $this->viewMock
            ]
        );
    }

    public function testExecute()
    {
        $this->menuMock->expects($this->once())->method('getParentItems')->willReturn([$this->menuItemMock]);
        $this->titleMock->expects($this->atLeastOnce())->method('prepend');
        $this->pageConfigMock->expects($this->atLeastOnce())->method('getTitle')->willReturn($this->titleMock);
        $this->pageMock->expects($this->atLeastOnce())->method('getConfig')->willReturn($this->pageConfigMock);
        $this->blockMock->expects($this->atLeastOnce())->method('addLink');
        $this->blockMock->expects($this->once())->method('setActive');
        $this->blockMock->expects($this->once())->method('getMenuModel')->willReturn($this->menuMock);
        $this->layoutMock->expects($this->atLeastOnce())->method('getBlock')->willReturn($this->blockMock);
        $this->viewMock->expects($this->once())->method('loadLayout')->willReturnSelf();
        $this->viewMock->expects($this->atLeastOnce())->method('getLayout')->willReturn($this->layoutMock);
        $this->viewMock->expects($this->atLeastOnce())->method('getPage')->willReturn($this->pageMock);

        $this->action->execute();
    }
}
