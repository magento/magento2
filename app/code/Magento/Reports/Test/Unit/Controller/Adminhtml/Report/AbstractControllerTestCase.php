<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Reports\Test\Unit\Controller\Adminhtml\Report;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Menu;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\ViewInterface;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\BlockInterface;
use Magento\Framework\View\LayoutInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
abstract class AbstractControllerTestCase extends TestCase
{
    /**
     * @var Context|MockObject
     */
    protected $contextMock;

    /**
     * @var FileFactory|MockObject
     */
    protected $fileFactoryMock;

    /**
     * @var RequestInterface|MockObject
     */
    protected $requestMock;

    /**
     * @var ViewInterface|MockObject
     */
    protected $viewMock;

    /**
     * @var LayoutInterface|MockObject
     */
    protected $layoutMock;

    /**
     * @var BlockInterface|MockObject
     */
    protected $breadcrumbsBlockMock;

    /**
     * @var BlockInterface|MockObject
     */
    protected $menuBlockMock;

    /**
     * @var BlockInterface|MockObject
     */
    protected $switcherBlockMock;

    /**
     * @var Menu|MockObject
     */
    protected $menuModelMock;

    /**
     * @var AbstractBlock|MockObject
     */
    protected $abstractBlockMock;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        $this->requestMock = $this->getMockForAbstractClassBuilder(
            RequestInterface::class,
            ['isDispatched', 'initForward', 'setDispatched', 'isForwarded']
        );
        $this->breadcrumbsBlockMock = $this->getMockForAbstractClassBuilder(
            BlockInterface::class,
            ['addLink']
        );
        $this->menuBlockMock = $this->getMockForAbstractClassBuilder(
            BlockInterface::class,
            ['setActive', 'getMenuModel']
        );
        $this->viewMock = $this->getMockForAbstractClassBuilder(
            ViewInterface::class
        );

        $this->layoutMock = $this->getMockBuilder(LayoutInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->switcherBlockMock = $this->getMockBuilder(BlockInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->fileFactoryMock = $this->getMockBuilder(FileFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->menuModelMock = $this->getMockBuilder(Menu::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->abstractBlockMock = $this->getMockBuilder(AbstractBlock::class)
            ->addMethods(['getCsvFile', 'getExcelFile', 'setSaveParametersInSession', 'getCsv', 'getExcel'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->menuModelMock->expects($this->any())->method('getParentItems')->willReturn([]);
        $this->menuBlockMock->expects($this->any())->method('getMenuModel')->willReturn($this->menuModelMock);
        $this->viewMock->expects($this->any())->method('getLayout')->willReturn($this->layoutMock);
        $this->contextMock->expects($this->any())->method('getRequest')->willReturn($this->requestMock);
        $this->contextMock->expects($this->any())->method('getView')->willReturn($this->viewMock);

        $this->layoutMock->expects($this->any())->method('getBlock')->willReturnMap(
            [
                ['breadcrumbs', $this->breadcrumbsBlockMock],
                ['menu', $this->menuBlockMock],
                ['store_switcher', $this->switcherBlockMock]
            ]
        );
        $this->layoutMock->expects($this->any())->method('getChildBlock')->willReturn($this->abstractBlockMock);
    }

    /**
     * Custom mock for abstract class
     * @param string $className
     * @param array $mockedMethods
     * @return MockObject
     */
    protected function getMockForAbstractClassBuilder($className, $mockedMethods = [])
    {
        return $this->getMockForAbstractClass($className, [], '', false, false, true, $mockedMethods);
    }
}
