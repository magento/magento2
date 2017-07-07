<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Test\Unit\Model\Config\Backend;

class ManagestockTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \Magento\CatalogInventory\Model\Indexer\Stock\Processor|\PHPUnit_Framework_MockObject_MockObject */
    protected $stockIndexerProcessor;

    /** @var \Magento\CatalogInventory\Model\Config\Backend\Managestock */
    protected $model;

    protected function setUp()
    {
        $this->stockIndexerProcessor = $this->getMockBuilder(
            \Magento\CatalogInventory\Model\Indexer\Stock\Processor::class
        )->disableOriginalConstructor()->getMock();
        $this->model = (new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this))->getObject(
            \Magento\CatalogInventory\Model\Config\Backend\Managestock::class,
            [
                'stockIndexerProcessor' => $this->stockIndexerProcessor,
            ]
        );
    }

    /**
     * Data provider for testSaveAndRebuildIndex
     * @return array
     */
    public function saveAndRebuildIndexDataProvider()
    {
        return [
            [1, 1],
            [0, 0],
        ];
    }

    /**
     * @dataProvider saveAndRebuildIndexDataProvider
     *
     * @param int $newStockValue new value for stock status
     * @param int $callCount count matcher
     */
    public function testSaveAndRebuildIndex($newStockValue, $callCount)
    {
        $this->model->setValue($newStockValue);
        $this->stockIndexerProcessor->expects($this->exactly($callCount))->method('markIndexerAsInvalid');
        $this->model->afterSave();
    }
}
