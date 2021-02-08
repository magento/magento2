<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AsynchronousOperations\Test\Unit\Controller\Adminhtml\Index;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class IndexTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $viewMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $requestMock;

    /**
     * @var \Magento\AsynchronousOperations\Controller\Adminhtml\Index\Index
     */
    private $model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $resultFactoryMock;

    protected function setUp(): void
    {
        $objectManager =  new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->viewMock = $this->createMock(\Magento\Framework\App\ViewInterface::class);
        $this->requestMock = $this->createMock(\Magento\Framework\App\RequestInterface::class);
        $this->resultFactoryMock = $this->createMock(\Magento\Framework\View\Result\PageFactory::class);

        $this->model = $objectManager->getObject(
            \Magento\AsynchronousOperations\Controller\Adminhtml\Index\Index::class,
            [
                'request' => $this->requestMock,
                'view' => $this->viewMock,
                'resultPageFactory' => $this->resultFactoryMock

            ]
        );
    }

    public function testExecute()
    {
        $itemId = 'Magento_AsynchronousOperations::system_magento_logging_bulk_operations';
        $prependText = 'Bulk Actions Log';
        $layoutMock = $this->createMock(\Magento\Framework\View\LayoutInterface::class);
        $menuModelMock = $this->createMock(\Magento\Backend\Model\Menu::class);
        $pageMock = $this->createMock(\Magento\Framework\View\Result\Page::class);
        $pageConfigMock = $this->createMock(\Magento\Framework\View\Page\Config::class);
        $titleMock = $this->createMock(\Magento\Framework\View\Page\Title::class);
        $this->resultFactoryMock->expects($this->once())->method('create')->willReturn($pageMock);

        $blockMock = $this->createPartialMock(
            \Magento\Framework\View\Element\BlockInterface::class,
            ['setActive', 'getMenuModel', 'toHtml']
        );

        $this->viewMock->expects($this->once())->method('getLayout')->willReturn($layoutMock);
        $layoutMock->expects($this->once())->method('getBlock')->willReturn($blockMock);
        $blockMock->expects($this->once())->method('setActive')->with($itemId);
        $blockMock->expects($this->once())->method('getMenuModel')->willReturn($menuModelMock);
        $menuModelMock->expects($this->once())->method('getParentItems')->willReturn([]);

        $pageMock->expects($this->once())->method('getConfig')->willReturn($pageConfigMock);
        $pageConfigMock->expects($this->once())->method('getTitle')->willReturn($titleMock);
        $titleMock->expects($this->once())->method('prepend')->with($prependText);
        $pageMock->expects($this->once())->method('initLayout');
        $this->model->execute();
    }
}
