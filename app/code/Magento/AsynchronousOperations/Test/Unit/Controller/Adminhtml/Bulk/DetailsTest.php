<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AsynchronousOperations\Test\Unit\Controller\Adminhtml\Bulk;

use Magento\AsynchronousOperations\Controller\Adminhtml\Bulk\Details;
use Magento\Backend\Model\Menu;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ViewInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\BlockInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\View\Page\Config;
use Magento\Framework\View\Page\Title;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DetailsTest extends TestCase
{
    /**
     * @var MockObject
     */
    private $viewMock;

    /**
     * @var MockObject
     */
    private $requestMock;

    /**
     * @var Details
     */
    private $model;

    /**
     * @var MockObject
     */
    private $resultFactoryMock;

    protected function setUp(): void
    {
        $objectManager =  new ObjectManager($this);
        $this->viewMock = $this->getMockForAbstractClass(ViewInterface::class);
        $this->requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $this->resultFactoryMock = $this->createMock(PageFactory::class);
        $this->model = $objectManager->getObject(
            Details::class,
            [
                'request' => $this->requestMock,
                'resultPageFactory' => $this->resultFactoryMock,
                'view' => $this->viewMock,

            ]
        );
    }

    public function testExecute()
    {
        $id = '42';
        $parameterName = 'uuid';
        $itemId = 'Magento_AsynchronousOperations::system_magento_logging_bulk_operations';
        $layoutMock = $this->getMockForAbstractClass(LayoutInterface::class);

        $blockMock = $this->getMockBuilder(BlockInterface::class)
            ->addMethods(['setActive', 'getMenuModel'])
            ->onlyMethods(['toHtml'])
            ->getMockForAbstractClass();
        $menuModelMock = $this->createMock(Menu::class);
        $this->viewMock->expects($this->once())->method('getLayout')->willReturn($layoutMock);
        $layoutMock->expects($this->once())->method('getBlock')->willReturn($blockMock);
        $blockMock->expects($this->once())->method('setActive')->with($itemId);
        $blockMock->expects($this->once())->method('getMenuModel')->willReturn($menuModelMock);
        $menuModelMock->expects($this->once())->method('getParentItems')->willReturn([]);
        $pageMock = $this->createMock(Page::class);
        $pageConfigMock = $this->createMock(Config::class);
        $titleMock = $this->createMock(Title::class);
        $this->resultFactoryMock->expects($this->once())->method('create')->willReturn($pageMock);
        $this->requestMock->expects($this->once())->method('getParam')->with($parameterName)->willReturn($id);
        $pageMock->expects($this->once())->method('getConfig')->willReturn($pageConfigMock);
        $pageConfigMock->expects($this->once())->method('getTitle')->willReturn($titleMock);
        $titleMock->expects($this->once())->method('prepend');
        $pageMock->expects($this->once())->method('initLayout');
        $this->assertEquals($pageMock, $this->model->execute());
    }
}
