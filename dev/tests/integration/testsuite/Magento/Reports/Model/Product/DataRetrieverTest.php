<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Reports\Model\Product;

use Magento\Catalog\Model\Indexer\Product\Price\Processor;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @magentoAppArea adminhtml
 */
class DataRetrieverTest extends TestCase
{
    /**
     * @var DataRetriever
     */
    private $dataRetriever;

    /**
     * @var Processor
     */
    private $priceIndexerProcessor;

    protected function setUp(): void
    {
        $this->dataRetriever = Bootstrap::getObjectManager()->create(DataRetriever::class);
        $this->priceIndexerProcessor = Bootstrap::getObjectManager()->get(Processor::class);
    }

    /**
     * Test retrieve products data for reports by entity id's
     * Do not use magentoDbIsolation because index statement changing "tears" transaction (triggers creating)
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoConfigFixture default/reports/options/enabled 1
     * @magentoDbIsolation disabled
     *
     * @return void
     */
    public function testExecute(): void
    {
        $productId = 1;
        $this->priceIndexerProcessor->reindexAll();
        $actualResult = $this->dataRetriever->execute([$productId]);
        $this->assertNotEmpty($actualResult);
        $this->assertCount(1, $actualResult);
        $this->assertEquals(10, $actualResult[$productId]['price']);
    }
}
