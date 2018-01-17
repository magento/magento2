<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Test\Integration;

use Magento\Framework\Indexer\IndexerInterface;
use Magento\InventoryApi\Api\StockRepositoryInterface;
use Magento\InventoryIndexer\Indexer\Source\SourceIndexer;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Represent ability to add sales chanel type website for stock.
 */
abstract class AbstractSalesChannelProvider extends TestCase
{
    /**
     * @var SalesChannelInterface
     */
    private $salesChannel;

    /**
     * @var StockRepositoryInterface
     */
    private $stockRepository;

    /**
     * @var IndexerInterface
     */
    private $indexer;

    /**
     * @var null|int
     */
    private $stockId = null;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->salesChannel = Bootstrap::getObjectManager()->get(SalesChannelInterface::class);
        $this->stockRepository = Bootstrap::getObjectManager()->get(StockRepositoryInterface::class);
        $this->indexer = Bootstrap::getObjectManager()->create(IndexerInterface::class);
        $this->indexer->load(SourceIndexer::INDEXER_ID);
        parent::setUp();
    }

    /**
     * Add sales channel type website to stock by stock id and website code.
     *
     * @param int $stockId
     * @param string $websiteCode
     *
     * @return void
     */
    protected function addSalesChannelTypeWebsiteToStock(int $stockId, string $websiteCode)
    {
        $this->salesChannel->setCode($websiteCode);
        $this->salesChannel->setType(SalesChannelInterface::TYPE_WEBSITE);

        $stock = $this->stockRepository->get($stockId);
        $stock->getExtensionAttributes()->setSalesChannels([$this->salesChannel]);

        $this->stockRepository->save($stock);
        $this->stockId = $stockId;

        $this->indexer->reindexAll();
    }

    /**
     * Empty sales channels for stock to be able to delete stock.
     */
    protected function tearDown()
    {
        parent::tearDown();

        if ($this->stockId !== null) {
            $stock = $this->stockRepository->get($this->stockId);
            $stock->getExtensionAttributes()->setSalesChannels([]);

            $this->stockRepository->save($stock);
        }
    }
}
