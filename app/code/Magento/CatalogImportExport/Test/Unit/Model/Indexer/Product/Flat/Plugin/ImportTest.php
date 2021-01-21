<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExport\Test\Unit\Model\Indexer\Product\Flat\Plugin;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ImportTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Flat\Processor|\PHPUnit\Framework\MockObject\MockObject
     */
    private $processorMock;

    /**
     * @var \Magento\CatalogImportExport\Model\Indexer\Product\Flat\Plugin\Import
     */
    private $model;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Flat\State|\PHPUnit\Framework\MockObject\MockObject
     */
    private $flatStateMock;

    /**
     * @var \Magento\ImportExport\Model\Import|\PHPUnit\Framework\MockObject\MockObject
     */
    private $subjectMock;

    protected function setUp(): void
    {
        $this->processorMock = $this->getMockBuilder(\Magento\Catalog\Model\Indexer\Product\Flat\Processor::class)
            ->disableOriginalConstructor()
            ->setMethods(['markIndexerAsInvalid', 'isIndexerScheduled'])
            ->getMock();

        $this->flatStateMock = $this->getMockBuilder(\Magento\Catalog\Model\Indexer\Product\Flat\State::class)
            ->disableOriginalConstructor()
            ->setMethods(['isFlatEnabled'])
            ->getMock();

        $this->subjectMock = $this->getMockBuilder(\Magento\ImportExport\Model\Import::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = (new ObjectManager($this))->getObject(
            \Magento\CatalogImportExport\Model\Indexer\Product\Flat\Plugin\Import::class,
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
