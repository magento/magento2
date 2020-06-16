<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogImportExport\Test\Unit\Model\Indexer\Product\Flat\Plugin;

use Magento\Catalog\Model\Indexer\Product\Flat\Processor;
use Magento\Catalog\Model\Indexer\Product\Flat\State;
use Magento\CatalogImportExport\Model\Indexer\Product\Flat\Plugin\Import;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ImportTest extends TestCase
{
    /**
     * @var Processor|MockObject
     */
    private $processorMock;

    /**
     * @var Import
     */
    private $model;

    /**
     * @var State|MockObject
     */
    private $flatStateMock;

    /**
     * @var \Magento\ImportExport\Model\Import|MockObject
     */
    private $subjectMock;

    protected function setUp(): void
    {
        $this->processorMock = $this->getMockBuilder(Processor::class)
            ->disableOriginalConstructor()
            ->setMethods(['markIndexerAsInvalid', 'isIndexerScheduled'])
            ->getMock();

        $this->flatStateMock = $this->getMockBuilder(State::class)
            ->disableOriginalConstructor()
            ->setMethods(['isFlatEnabled'])
            ->getMock();

        $this->subjectMock = $this->getMockBuilder(\Magento\ImportExport\Model\Import::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = (new ObjectManager($this))->getObject(
            Import::class,
            [
                'productFlatIndexerProcessor' => $this->processorMock,
                'flatState' => $this->flatStateMock
            ]
        );
    }

    public function testAfterImportSourceWithFlatEnabledAndIndexerScheduledDisabled()
    {
        $this->flatStateMock->expects($this->once())->method('isFlatEnabled')->willReturn(true);
        $this->processorMock->expects($this->once())->method('isIndexerScheduled')->willReturn(false);
        $this->processorMock->expects($this->once())->method('markIndexerAsInvalid');
        $someData = [1, 2, 3];
        $this->assertEquals($someData, $this->model->afterImportSource($this->subjectMock, $someData));
    }

    public function testAfterImportSourceWithFlatDisabledAndIndexerScheduledDisabled()
    {
        $this->flatStateMock->expects($this->once())->method('isFlatEnabled')->willReturn(false);
        $this->processorMock->expects($this->never())->method('isIndexerScheduled')->willReturn(false);
        $this->processorMock->expects($this->never())->method('markIndexerAsInvalid');
        $someData = [1, 2, 3];
        $this->assertEquals($someData, $this->model->afterImportSource($this->subjectMock, $someData));
    }

    public function testAfterImportSourceWithFlatEnabledAndIndexerScheduledEnabled()
    {
        $this->flatStateMock->expects($this->once())->method('isFlatEnabled')->willReturn(true);
        $this->processorMock->expects($this->once())->method('isIndexerScheduled')->willReturn(true);
        $this->processorMock->expects($this->never())->method('markIndexerAsInvalid');
        $someData = [1, 2, 3];
        $this->assertEquals($someData, $this->model->afterImportSource($this->subjectMock, $someData));
    }
}
