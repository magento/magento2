<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Block\Cart;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Magento\CatalogInventory\Model\StockRegistryProvider;
use Magento\Checkout\Model\Session;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;

class AbstractCartTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests that product in shopping cart remains visible even after it becomes out of stock.
     *
     * @magentoDataFixture Magento/Sales/_files/quote.php
     */
    public function testProductsOutOfStockIsVisibleInShoppingCart()
    {
        $productSku = 'simple';
        $reservedOrderId = 'test01';
        $objectManager = Bootstrap::getObjectManager();

        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $objectManager->get(ProductRepositoryInterface::class);
        $product = $productRepository->get($productSku);

        $this->setProductOutOfStock($product->getId());

        $quote = $this->getStoredQuote($reservedOrderId);

        $checkoutSession = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->getMock();
        $checkoutSession->method('getQuote')->willReturn($quote);

        $abstractCart = $objectManager->create(
            AbstractCart::class,
            ['checkoutSession' => $checkoutSession]
        );

        $items = array_filter(
            $abstractCart->getItems(),
            function (\Magento\Quote\Model\Quote\Item $item) use ($productSku) {
                return $item->getSku() === $productSku;
            }
        );

        $this->assertNotEmpty(
            $items,
            'Product disappeared from shopping cart after it had become out of stock.'
        );
    }

    /**
     * @param int $productId
     * @return void
     */
    private function setProductOutOfStock($productId)
    {
        /** @var $stockRegistryProvider StockRegistryProvider */
        $stockRegistryProvider = Bootstrap::getObjectManager()->get(StockRegistryProvider::class);
        $stockItem = $stockRegistryProvider->getStockItem($productId, 0);
        $stockItem->setIsInStock(false);

        /** @var $stockItemRepository StockItemRepositoryInterface */
        $stockItemRepository = Bootstrap::getObjectManager()->get(StockItemRepositoryInterface::class);
        $stockItemRepository->save($stockItem);
    }

    /**
     * @param string $reservedOrderId
     * @return \Magento\Quote\Api\Data\CartInterface
     */
    private function getStoredQuote($reservedOrderId)
    {
        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = Bootstrap::getObjectManager()->get(SearchCriteriaBuilder::class);
        $searchCriteriaBuilder->addFilter('reserved_order_id', $reservedOrderId);

        /** @var CartRepositoryInterface $cartRepository */
        $cartRepository = Bootstrap::getObjectManager()->create(CartRepositoryInterface::class);
        $quoteItems = $cartRepository->getList($searchCriteriaBuilder->create())->getItems();

        return current($quoteItems);
    }
}
