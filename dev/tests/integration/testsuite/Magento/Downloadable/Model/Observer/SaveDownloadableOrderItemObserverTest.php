<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Downloadable\Model\Observer;

use Magento\Downloadable\Model\Link\Purchased\Item;
use Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item\Collection;
use Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item\CollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Item as OrderItem;
use Magento\Sales\Model\Order\ItemRepository;
use Magento\Store\Model\ScopeInterface;
use Magento\TestFramework\Downloadable\Model\RemoveLinkPurchasedByOrderIncrementId;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test for case, when customer is able to download downloadable product.
 */
class SaveDownloadableOrderItemObserverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Initialization of dependencies
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
    }

    /**
     * Asserting, that links status is 'Available' when order is in processing state,
     * and 'Order Item Status to Enable Downloads' is 'Invoiced'.
     *
     * @magentoDataFixture Magento/Downloadable/_files/order_with_customer_and_downloadable_product.php
     * @magentoDataFixture Magento/Downloadable/_files/customer_order_with_invoice_downloadable_product.php
     */
    public function testOrderStateIsProcessingAndInvoicedOrderItemLinkIsDownloadable()
    {
        $orderIncremetId = '100000001';
        /** @var Order $order */
        $order = $this->objectManager->create(Order::class);
        $order->loadByIncrementId($orderIncremetId);
        /** @var OrderItem $orderItem */
        $orderItem = current($order->getAllItems());
        $config = $this->objectManager->get(ScopeConfigInterface::class);
        $orderItemStatusToEnableDownload = $config->getValue(
            \Magento\Downloadable\Model\Link\Purchased\Item::XML_PATH_ORDER_ITEM_STATUS,
            ScopeInterface::SCOPE_STORE,
            $orderItem->getStoreId()
        );

        /** Remove downloadable links from order item to create them from scratch */
        $removeLinkPurchasedByOrderIncrementId = $this->objectManager->get(
            RemoveLinkPurchasedByOrderIncrementId::class
        );
        $removeLinkPurchasedByOrderIncrementId->execute($orderIncremetId);

        $this->assertEquals(Order::STATE_PROCESSING, $order->getState());
        $this->assertEquals(OrderItem::STATUS_INVOICED, $orderItem->getStatusId());
        $this->assertEquals(OrderItem::STATUS_INVOICED, $orderItemStatusToEnableDownload);

        /** Save order item to trigger observers */
        $orderItemRepository = $this->objectManager->get(ItemRepository::class);
        $orderItemRepository->save($orderItem);

        $this->assertOrderItemLinkStatus((int)$orderItem->getId(), Item::LINK_STATUS_AVAILABLE);
    }

    /**
     * Assert that order item link status is expected.
     *
     * @param int $orderItemId
     * @param string $linkStatus
     * @return void
     */
    public function assertOrderItemLinkStatus(int $orderItemId, string $linkStatus): void
    {
        /** @var Collection $linkCollection */
        $linkCollection = $this->objectManager->create(CollectionFactory::class)->create();
        $linkCollection->addFieldToFilter('order_item_id', $orderItemId);

        /** Assert there are items in linkCollection to avoid false-positive test result. */
        $this->assertGreaterThan(0, $linkCollection->count());

        /** @var Item $linkItem */
        foreach ($linkCollection->getItems() as $linkItem) {
            $this->assertEquals(
                $linkStatus,
                $linkItem->getStatus()
            );
        }
    }
}
