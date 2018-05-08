<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Test\Integration\Stock;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\InventoryAdminUi\Model\Stock\StockSourceLinkProcessor;
use Magento\InventoryApi\Api\Data\StockSourceLinkInterface;
use Magento\InventoryApi\Api\GetStockSourceLinksInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class StockSourceLinkProcessorTest extends TestCase
{
    /**
     * @var StockSourceLinkProcessor
     */
    private $stockSourceLinkProcessor;

    /**
     * @var SortOrderBuilder
     */
    private $sortOrderBuilder;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var GetStockSourceLinksInterface
     */
    private $getStockSourceLinks;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->searchCriteriaBuilder = Bootstrap::getObjectManager()->get(SearchCriteriaBuilder::class);
        $this->getStockSourceLinks = Bootstrap::getObjectManager()->get(GetStockSourceLinksInterface::class);
        $this->stockSourceLinkProcessor = Bootstrap::getObjectManager()->get(StockSourceLinkProcessor::class);
        $this->sortOrderBuilder = Bootstrap::getObjectManager()->get(SortOrderBuilder::class);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     */
    public function testProcess()
    {
        /**
         * eu-3 - should be updated
         * us-1 - should be added
         * eu-2, eu-disabled - should be removed
         */
        $linksData = [
            [
                StockSourceLinkInterface::SOURCE_CODE => 'eu-3',
                StockSourceLinkInterface::PRIORITY => 1,
            ],
            [
                StockSourceLinkInterface::SOURCE_CODE => 'us-1',
                StockSourceLinkInterface::PRIORITY => 2,
            ],
        ];
        $stockId = 10;

        $this->stockSourceLinkProcessor->process($stockId, $linksData);

        $sortOrder = $this->sortOrderBuilder
            ->setField(StockSourceLinkInterface::PRIORITY)
            ->setAscendingDirection()
            ->create();
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(StockSourceLinkInterface::STOCK_ID, $stockId)
            ->addSortOrder($sortOrder)
            ->create();
        $searchResult = $this->getStockSourceLinks->execute($searchCriteria);

        self::assertCount(2, $searchResult->getItems());
    }
}
