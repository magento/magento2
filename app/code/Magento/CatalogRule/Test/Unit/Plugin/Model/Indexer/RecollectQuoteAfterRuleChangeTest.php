<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogRule\Test\Unit\Plugin\Model\Indexer;

use Magento\Catalog\Model\Indexer\Product\Price;
use Magento\CatalogRule\Plugin\Model\Indexer\RecollectQuoteAfterRuleChange;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResourceModel;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RecollectQuoteAfterRuleChangeTest extends TestCase
{
    /**
     * @var QuoteResourceModel&MockObject
     */
    private $quoteResourceModel;

    /**
     * @var Price&MockObject
     */
    private $priceIndexer;

    /**
     * @var RecollectQuoteAfterRuleChange
     */
    private $plugin;

    protected function setUp(): void
    {
        $this->quoteResourceModel = $this->createMock(QuoteResourceModel::class);
        $this->priceIndexer = $this->createMock(Price::class);
        $this->plugin = new RecollectQuoteAfterRuleChange($this->quoteResourceModel);
    }

    public function testAfterExecuteMarksQuotesForRecollect(): void
    {
        $this->quoteResourceModel->expects($this->once())->method('markQuotesRecollect')->with([1, 2]);

        $this->plugin->afterExecute($this->priceIndexer, null, [1, 2]);
    }

    public function testAfterExecuteListMarksQuotesForRecollect(): void
    {
        $this->quoteResourceModel->expects($this->once())->method('markQuotesRecollect')->with([1, 2]);

        $this->plugin->afterExecuteList($this->priceIndexer, null, [1, 2]);
    }

    public function testAfterExecuteRowMarksQuotesForRecollect(): void
    {
        $this->quoteResourceModel->expects($this->once())->method('markQuotesRecollect')->with([5]);

        $this->plugin->afterExecuteRow($this->priceIndexer, null, 5);
    }
}
