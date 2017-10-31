<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\InventoryCatalog\Test\Integration\StockRepository;

use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Magento\InventoryApi\Api\StockRepositoryInterface;

/**
 * Class CouldNotDeleteStockAssignToSalesChannelsExceptionTest
 */
class CouldNotDeleteStockAssignToSalesChannelsExceptionTest extends TestCase
{
    /**
     * @var StockRepositoryInterface
     */
    private $stockRepository;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->stockRepository = Bootstrap::getObjectManager()->get(StockRepositoryInterface::class);
    }

    /**
     * Test that Stock assigned to sales channels could not be deleted
     *
     * @magentoApiDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock.php
     */
    public function testCouldNotDeleteException()
    {
        try {
            $this->stockRepository->deleteById(10);
            $this->fail('Expected throwing exception');
        } catch (\Exception $e) {
            self::assertEquals('Stock has at least one sale channel and could not be deleted.', $e->getMessage());
        }
    }
}
