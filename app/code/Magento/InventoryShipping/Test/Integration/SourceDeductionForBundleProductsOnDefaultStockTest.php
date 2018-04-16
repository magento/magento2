<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShipping\Test\Integration;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use PHPUnit\Framework\TestCase;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\InventoryCatalog\Api\DefaultStockProviderInterface;
use Magento\InventorySalesApi\Api\GetProductSalableQtyInterface;
use Magento\Inventory\Model\SourceItem\Command\GetSourceItemsBySkuInterface;
use Magento\Catalog\Model\Product\Type\AbstractType;
use Magento\Sales\Api\ShipOrderInterface;
use Magento\Sales\Api\Data\ShipmentItemCreationInterfaceFactory;
use Magento\Sales\Api\Data\ShipmentItemCreationInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SourceDeductionForBundleProductsOnDefaultStockTest extends TestCase
{
    /**
     * @var DefaultStockProviderInterface
     */
    private $defaultStockProvider;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var GetProductSalableQtyInterface
     */
    private $getProductSalableQty;

    /**
     * @var GetSourceItemsBySkuInterface
     */
    private $getSourceItemBySku;

    /**
     * @var ShipOrderInterface
     */
    private $shipOrder;

    /**
     * @var ShipmentItemCreationInterfaceFactory
     */
    private $shipmentItemCreationFactory;

    protected function setUp()
    {
        $this->searchCriteriaBuilder = Bootstrap::getObjectManager()->get(SearchCriteriaBuilder::class);
        $this->orderRepository = Bootstrap::getObjectManager()->get(OrderRepositoryInterface::class);
        $this->getSourceItemBySku = Bootstrap::getObjectManager()->get(GetSourceItemsBySkuInterface::class);
        $this->defaultStockProvider = Bootstrap::getObjectManager()->get(DefaultStockProviderInterface::class);
        $this->getProductSalableQty = Bootstrap::getObjectManager()->get(GetProductSalableQtyInterface::class);
        $this->shipOrder = Bootstrap::getObjectManager()->get(ShipOrderInterface::class);
        $this->shipmentItemCreationFactory = Bootstrap::getObjectManager()
            ->get(ShipmentItemCreationInterfaceFactory::class);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryCatalog/Test/_files/source_items_on_default_source.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryShipping/Test/_files/products_bundle.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryShipping/Test/_files/order_bundle_products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     */
    public function testSourceDeductionWhileInvoicingPartialOrderedQty()
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('increment_id', 'test_order_bundle_1')
            ->create();
        /** @var OrderInterface $order */
        $order = current($this->orderRepository->getList($searchCriteria)->getItems());

        $items = [];

        /** @var \Magento\Sales\Model\Order\Item $item */
        foreach ($order->getAllVisibleItems() as $item) {
            /** @var ShipmentItemCreationInterface $invoiceItemCreation */
            $shipmentItemCreation = $this->shipmentItemCreationFactory->create();
            $shipmentItemCreation->setOrderItemId($item->getId());
            $shipmentItemCreation->setQty(1);
            $items[] = $shipmentItemCreation;
            if ($item->getProduct()->getShipmentType() == AbstractType::SHIPMENT_SEPARATELY) {
                foreach ($item->getChildrenItems() as $childrenItem) {
                    /** @var ShipmentItemCreationInterface $invoiceItemCreation */
                    $shipmentItemCreation = $this->shipmentItemCreationFactory->create();
                    $shipmentItemCreation->setOrderItemId($childrenItem->getId());
                    $shipmentItemCreation->setQty(2);
                    $items[] = $shipmentItemCreation;
                }
            }
        }
        $this->shipOrder->execute($order->getEntityId(), $items);

        /** @var SourceItemInterface $sourceItem */
        $sourceItem = current($this->getSourceItemBySku->execute('SKU-2')->getItems());
        self::assertEquals(3, $sourceItem->getQuantity());

        $sourceItem = current($this->getSourceItemBySku->execute('SKU-1')->getItems());
        self::assertEquals(3.5, $sourceItem->getQuantity());

        $salableQty = $this->getProductSalableQty->execute('SKU-2', $this->defaultStockProvider->getId());
        self::assertEquals(2, $salableQty);

        $salableQty = $this->getProductSalableQty->execute('SKU-1', $this->defaultStockProvider->getId());
        self::assertEquals(1.5, $salableQty);
    }
}
