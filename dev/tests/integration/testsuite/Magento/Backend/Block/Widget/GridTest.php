<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\Block\Widget;

use Magento\Backend\Block\Widget\Grid\ColumnSet;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Model\Widget\Grid\Row\UrlGeneratorFactory;
use Magento\Backend\Model\Widget\Grid\SubTotals;
use Magento\Backend\Model\Widget\Grid\Totals;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Json\Helper\Data;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Layout;
use Magento\Framework\View\LayoutInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @magentoAppArea adminhtml
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GridTest extends TestCase
{
    /**
     * @var ColumnSet
     */
    private $block;

    /**
     * @var LayoutInterface|MockObject
     */
    private $layoutMock;

    /**
     * @var ColumnSet|MockObject
     */
    private $columnSetMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->layoutMock = $this->getMockBuilder(Layout::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->onlyMethods(['getChildName', 'getBlock', 'createBlock', 'renameElement', 'unsetChild', 'setChild'])
            ->addMethods(['helper'])
            ->getMock();

        $this->columnSetMock = $this->getColumnSetMock();

        $returnValueMap = [
            ['grid', 'grid.columnSet', 'grid.columnSet'],
            ['grid', 'reset_filter_button', 'reset_filter_button'],
            ['grid', 'search_button', 'search_button'],
        ];
        $this->layoutMock->expects($this->any())
            ->method('getChildName')
            ->willReturnMap($returnValueMap);

        $this->layoutMock->expects($this->any())
            ->method('getBlock')
            ->with('grid.columnSet')
            ->willReturn($this->columnSetMock);
        $this->layoutMock->expects($this->any())
            ->method('createBlock')
            ->with(Button::class)
            ->willReturn(Bootstrap::getObjectManager()->get(LayoutInterface::class)->createBlock(Button::class));
        $this->layoutMock->expects($this->any())
            ->method('helper')->with(Data::class)
            ->willReturn(Bootstrap::getObjectManager()->get(Data::class));

        $this->block = Bootstrap::getObjectManager()
            ->get(LayoutInterface::class)
            ->createBlock(Grid::class);

        $this->block->setLayout($this->layoutMock);
        $this->block->setNameInLayout('grid');
    }

    /**
     * Retrieve the mocked column set block instance
     *
     * @return ColumnSet|MockObject
     */
    private function getColumnSetMock()
    {
        $objectManager = Bootstrap::getObjectManager();
        $directoryList = $objectManager->create(
            DirectoryList::class,
            ['root' => __DIR__]
        );
        return $this->getMockBuilder(ColumnSet::class)
            ->setConstructorArgs(
                [
                    $objectManager->create(
                        \Magento\Framework\View\Element\Template\Context::class,
                        [
                            'filesystem' => $objectManager->create(
                                Filesystem::class,
                                ['directoryList' => $directoryList]
                            )
                        ]
                    ),
                    $objectManager->create(UrlGeneratorFactory::class),
                    $objectManager->create(SubTotals::class),
                    $objectManager->create(Totals::class)
                ]
            )
            ->getMock();
    }

    /**
     * @return void
     */
    public function testToHtmlPreparesColumns(): void
    {
        $this->columnSetMock->expects($this->once())->method('setRendererType');
        $this->columnSetMock->expects($this->once())->method('setFilterType');
        $this->columnSetMock->expects($this->once())->method('setSortable');
        $this->block->setColumnRenderers(['filter' => 'Filter_Class']);
        $this->block->setColumnFilters(['filter' => 'Filter_Class']);
        $this->block->setSortable(false);
        $this->block->toHtml();
    }

    /**
     * @return void
     */
    public function testGetMainButtonsHtmlReturnsEmptyStringIfFiltersArentVisible(): void
    {
        $this->columnSetMock->expects($this->once())->method('isFilterVisible')->willReturn(false);
        $this->block->getMainButtonsHtml();
    }

    /**
     * @return void
     */
    public function testGetMassactionBlock(): void
    {
        /** @var $layout Layout */
        $layout = Bootstrap::getObjectManager()->get(
            LayoutInterface::class
        );
        /** @var $block Grid */
        $block = $layout->createBlock(Extended::class, 'block');
        $child = $layout->addBlock(Template::class, 'massaction', 'block');
        $this->assertSame($child, $block->getMassactionBlock());
    }
}
