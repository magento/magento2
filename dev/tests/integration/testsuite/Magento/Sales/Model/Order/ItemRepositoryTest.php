<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Model\Order;

class ItemRepositoryTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Sales\Model\Order */
    private $order;

    /** @var \Magento\Sales\Api\OrderItemRepositoryInterface */
    private $orderItemRepository;

    /** @var \Magento\Framework\Api\SearchCriteriaBuilder */
    private $searchCriteriaBuilder;

    protected function setUp(): void
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $this->order = $objectManager->create(\Magento\Sales\Model\Order::class);
        $this->orderItemRepository = $objectManager->create(\Magento\Sales\Api\OrderItemRepositoryInterface::class);
        $this->searchCriteriaBuilder = $objectManager->create(\Magento\Framework\Api\SearchCriteriaBuilder::class);
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order_configurable_product.php
     */
    public function testAddOrderItemParent()
    {
        $this->order->load('100000001', 'increment_id');

        foreach ($this->order->getItems() as $item) {
            if ($item->getProductType() === \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE) {
                $orderItem = $this->orderItemRepository->get($item->getItemId());
                $this->assertInstanceOf(\Magento\Sales\Api\Data\OrderItemInterface::class, $orderItem->getParentItem());
            }
        }

        $itemList = $this->orderItemRepository->getList(
            $this->searchCriteriaBuilder->addFilter('order_id', $this->order->getId())->create()
        );

        foreach ($itemList->getItems() as $item) {
            if ($item->getProductType() === \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE) {
                $this->assertInstanceOf(\Magento\Sales\Api\Data\OrderItemInterface::class, $item->getParentItem());
            }
        }
    }
}
