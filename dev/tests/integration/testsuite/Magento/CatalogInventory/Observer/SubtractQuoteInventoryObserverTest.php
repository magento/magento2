<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogInventory\Observer;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class SubtractQuoteInventoryObserverTest extends TestCase
{
    /**
     * @var CartManagementInterface
     */
    private $cartManagement;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepo;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $this->cartManagement = Bootstrap::getObjectManager()
            ->get(CartManagementInterface::class);
        $this->orderRepo = Bootstrap::getObjectManager()
            ->get(OrderRepositoryInterface::class);
        $this->cartRepository = Bootstrap::getObjectManager()
            ->get(CartRepositoryInterface::class);
    }

    /**
     * Check if order items are being marked as backorders.
     *
     * @throws CouldNotSaveException
     *
     * @return void
     *
     * @magentoAppArea adminhtml
     * @magentoAppIsolation enabled
     * @magentoConfigFixture cataloginventory/item_options/backorders 1
     * @magentoDataFixture Magento/Sales/_files/quote_with_bundle.php
     */
    public function testBackorder()
    {
        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = Bootstrap::getObjectManager()
            ->create(SearchCriteriaBuilder::class);
        $searchCriteria = $searchCriteriaBuilder
            ->addFilter('reserved_order_id', 'test01')
            ->create();
        $found = $this->cartRepository->getList($searchCriteria)->getItems();
        /** @var CartInterface $cart */
        $cart = array_pop($found);
        //Setting quantity over inventory capacity.
        $cart->getItems()[0]->setQty(102.00);
        $this->cartRepository->save($cart);

        //placing order and checking that items are marked as backorders.
        $orderId = $this->cartManagement->placeOrder($cart->getId());
        $order = $this->orderRepo->get($orderId);
        $item = $order->getItems()[2];
        $this->assertEquals(2, $item->getQtyBackordered());
    }
}
