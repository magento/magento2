<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryReservationCli\Test\Integration\Model;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ObjectManager;
use Magento\InventoryCatalogApi\Api\DefaultStockProviderInterface;
use Magento\InventoryReservationCli\Command\Input\GetReservationFromCompensationArgument;
use Magento\InventoryReservationCli\Model\GetSalableQuantityInconsistencies;
use Magento\InventoryReservationsApi\Model\AppendReservationsInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Verify compensations will be created correctly for missing reservations.
 */
class AppendReservationsTest extends TestCase
{
    /**
     * @var GetSalableQuantityInconsistencies
     */
    private $getSalableQuantityInconsistencies;

    /**
     * @var GetReservationFromCompensationArgument
     */
    private $getReservationFromCompensationArgument;

    /**
     * @var AppendReservationsInterface
     */
    private $appendReservations;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $this->getSalableQuantityInconsistencies = Bootstrap::getObjectManager()
            ->get(GetSalableQuantityInconsistencies::class);
        $this->getReservationFromCompensationArgument = Bootstrap::getObjectManager()
            ->get(GetReservationFromCompensationArgument::class);
        $this->appendReservations = Bootstrap::getObjectManager()->get(AppendReservationsInterface::class);
    }

    /**
     * Verify create-compensations command will correctly compensate qty for configurable product default stock.
     *
     * @magentoDataFixture ../../../../app/code/Magento/InventoryReservationCli/Test/Integration/_files/incomplete_order_without_reservation_configurable_product.php
     *
     * @see https://app.hiptest.com/projects/69435/test-plan/folders/909285/scenarios/3529620
     * @return void
     */
    public function testCompensateMissingReservationsConfigurableProductDefaultStock(): void
    {
        $stockId = Bootstrap::getObjectManager()->get(DefaultStockProviderInterface::class)->getId();
        $inconsistencies = $this->getSalableQuantityInconsistencies->execute();
        $items = reset($inconsistencies)->getItems();
        $argument = '100000001:simple_10:-' . $items['simple_10'] . ':' . $stockId;
        $reservation = $this->getReservationFromCompensationArgument->execute($argument);
        $this->appendReservations->execute([$reservation]);
        $inconsistencies = $this->getSalableQuantityInconsistencies->execute();
        self::assertCount(0, $inconsistencies);
    }

    /**
     * Verify create-compensations will correctly compensate qty for configurable product custom stock.
     *
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryConfigurableProduct/Test/_files/product_configurable.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/stock_website_sales_channels.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryConfigurableProduct/Test/_files/source_items_configurable.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryShipping/Test/_files/create_quote_on_us_website.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryShipping/Test/_files/order_configurable_product.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryReservationCli/Test/Integration/_files/delete_reservations.php
     * @magentoDbIsolation disabled
     *
     * @see https://app.hiptest.com/projects/69435/test-plan/folders/909285/scenarios/3529695
     * @return void
     */
    public function testCompensateMissingReservationsConfigurableProductCustomStock(): void
    {
        $orderRepository = ObjectManager::getInstance()->get(OrderRepositoryInterface::class);
        $searchCriteriaBuilder = ObjectManager::getInstance()->get(SearchCriteriaBuilder::class);
        $searchCriteria = $searchCriteriaBuilder
            ->addFilter('increment_id', 'created_order_for_test')
            ->create();
        $order = current($orderRepository->getList($searchCriteria)->getItems());
        $inconsistencies = $this->getSalableQuantityInconsistencies->execute();
        $items = reset($inconsistencies)->getItems();
        $argument = $order->getIncrementId() . ':simple_10:-' . $items['simple_10'] . ':20';
        $reservation = $this->getReservationFromCompensationArgument->execute($argument);
        $this->appendReservations->execute([$reservation]);
        $inconsistencies = $this->getSalableQuantityInconsistencies->execute();
        self::assertCount(0, $inconsistencies);
    }
}
