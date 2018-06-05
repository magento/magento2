<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExport\Test\Unit\Model\Indexer\Product\Flat\Plugin;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ImportTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Flat\Processor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $processorMock;

    /**
     * @var \Magento\CatalogImportExport\Model\Indexer\Product\Flat\Plugin\Import
     */
    private $model;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Flat\State|\PHPUnit_Framework_MockObject_MockObject
     */
    private $flatStateMock;

    /**
     * @var \Magento\ImportExport\Model\Import|\PHPUnit_Framework_MockObject_MockObject
     */
    private $subjectMock;

    protected function setUp()
    {
        $this->processorMock = $this->getMock(
            'Magento\Catalog\Model\Indexer\Product\Flat\Processor',
            ['markIndexerAsInvalid', 'isIndexerScheduled'],
            [],
            '',
            false
        );

        $this->flatStateMock = $this->getMock(
            '\Magento\Catalog\Model\Indexer\Product\Flat\State',
            ['isFlatEnabled'],
            [],
            '',
            false
        );

        $this->subjectMock = $this->getMock(
            'Magento\ImportExport\Model\Import',
            [],
            [],
            '',
            false
        );

        $this->model = (new ObjectManager($this))->getObject(
            'Magento\CatalogImportExport\Model\Indexer\Product\Flat\Plugin\Import',
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
