<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogInventory\Test\Unit\Model\Config\Backend;

use Magento\CatalogInventory\Model\Config\Backend\Managestock;
use Magento\CatalogInventory\Model\Indexer\Stock\Processor;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ManagestockTest extends TestCase
{
    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $configMock;

    /** @var  Processor|MockObject */
    protected $stockIndexerProcessor;

    /** @var Managestock */
    protected $model;

    protected function setUp(): void
    {
        $this->stockIndexerProcessor = $this->getMockBuilder(
            Processor::class
        )->disableOriginalConstructor()
            ->getMock();
        $this->configMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = (new ObjectManager($this))->getObject(
            Managestock::class,
            [
                'config' => $this->configMock,
                'stockIndexerProcessor' => $this->stockIndexerProcessor
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
        $this->configMock->method('getValue')->willReturn(0);   // old value for stock status

        $this->model->afterSave();
    }
}
