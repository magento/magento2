<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AdvancedPricingImportExport\Test\Unit\Model\Indexer\Product\Price\Plugin;

use \Magento\AdvancedPricingImportExport\Model\Indexer\Product\Price\Plugin\Import as Import;

class ImportTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Indexer\IndexerInterface |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $indexer;

    /**
     * @var Import |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $import;

    /**
     * @var \Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $advancedPricing;

    protected function setUp()
    {
        $this->indexer = $this->getMockForAbstractClass(
            \Magento\Framework\Indexer\IndexerInterface::class,
            [],
            '',
            false
        );
        $this->import = $this->getMock(
            \Magento\AdvancedPricingImportExport\Model\Indexer\Product\Price\Plugin\Import::class,
            ['getPriceIndexer', 'invalidateIndexer'],
            [],
            '',
            false
        );
        $this->advancedPricing = $this->getMock(
            \Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing::class,
            [],
            [],
            '',
            false
        );
        $this->import->expects($this->any())->method('getPriceIndexer')->willReturn($this->indexer);
    }

    public function testAfterSaveAdvancedPricing()
    {
        $this->indexer->expects($this->once())->method('isScheduled')->willReturn(false);
        $this->import->expects($this->once())->method('invalidateIndexer');

        $this->import->afterSaveAdvancedPricing($this->advancedPricing);
    }

    public function testAfterDeleteAdvancedPricing()
    {
        $this->indexer->expects($this->once())->method('isScheduled')->willReturn(false);
        $this->import->expects($this->once())->method('invalidateIndexer');

        $this->import->afterSaveAdvancedPricing($this->advancedPricing);
    }
}
