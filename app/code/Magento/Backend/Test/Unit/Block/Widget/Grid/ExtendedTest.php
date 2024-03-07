<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

/**
 * Test class for \Magento\Backend\Model\Url
 */
namespace Magento\Backend\Test\Unit\Block\Widget\Grid;

use Magento\Backend\Block\Widget\Grid\ColumnSet;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Data\Collection;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Layout;
use PHPUnit\Framework\TestCase;

class ExtendedTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $_objectManager;

    protected function setUp(): void
    {
        $this->_objectManager = new ObjectManager($this);
        $objects = [
            [
                JsonHelper::class,
                $this->createMock(JsonHelper::class)
            ],
            [
                DirectoryHelper::class,
                $this->createMock(DirectoryHelper::class)
            ]
        ];
        $this->_objectManager->prepareObjectManager($objects);
    }

    public function testPrepareLoadedCollection()
    {
        $request = $this->createPartialMock(Http::class, ['has']);
        $request->expects($this->any())->method('has')->willReturn(null);

        $columnSet = $this->createMock(ColumnSet::class);
        $layout = $this->createMock(Layout::class);
        $layout->expects($this->any())->method('getChildName')->willReturn('grid.columnSet');
        $layout->expects($this->any())->method('getBlock')->willReturn($columnSet);

        $collection = $this->createMock(Collection::class);
        $collection->expects($this->never())->method('isLoaded');
        $collection->expects($this->never())->method('clear');
        $collection->expects($this->atLeastOnce())->method('load');

        /** @var Extended $block */
        $block = $this->_objectManager->getObject(
            Extended::class,
            ['request' => $request, 'layout' => $layout]
        );

        $block->setCollection($collection);
        $block->getPreparedCollection();
    }
}
