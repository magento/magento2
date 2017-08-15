<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Inventory\Indexer;

use Magento\TestFramework\Helper\Bootstrap;

class StockItemTest extends \PHPUnit\Framework\TestCase
{


    /**
     * @var StockItemIndexerInterface
     */
    private $indexer;

    /**
     *
     */
    protected function setUp()
    {
        /** @var \Magento\Framework\Indexer\IndexerInterface indexer */
        $this->indexer = Bootstrap::getObjectManager()->create(
            \Magento\Indexer\Model\Indexer::class
        );
        $this->indexer->load(StockItemIndexerInterface::INDEXER_ID);
    }


    /**
     * @magentoDbIsolation disabled
     * @magentoDataFixture Magento/Inventory/_files/products.php
     * @magentoDataFixture Magento/Inventory/_files/source.php
     * @magentoDataFixture Magento/Inventory/_files/source_item.php
     */
     public function testExecuteFull()
     {

     }




}