<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Test\Integration\Stock;

use Magento\Framework\Api\CriteriaInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Inventory\Controller\Adminhtml\Stock\StockSourceLinkProcessor;
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
     * @var FilterBuilder
     */
    private $filterBuilder;

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
        $this->filterBuilder = Bootstrap::getObjectManager()->get(FilterBuilder::class);
    }

    /**
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
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

        $filter = $this->filterBuilder
            ->setField(StockSourceLinkInterface::STOCK_ID)
            ->setValue($stockId)
            ->create();

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter($filter)
            ->addSortOrder(StockSourceLinkInterface::PRIORITY, CriteriaInterface::SORT_ORDER_ASC)
            ->create();

        $searchResult = $this->getStockSourceLinks->execute($searchCriteria);

        $links = $searchResult->getItems();

        self::assertCount(2, $links);
    }
}
