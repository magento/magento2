<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Type;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\ExpectationFailedException;

/**
 * Class for testing QuoteManagement model
 */
class QuoteManagementTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var CartManagementInterface
     */
    private $cartManagement;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();

        $this->cartManagement = $this->objectManager->create(CartManagementInterface::class);
    }

    /**
     * Creates order with product that has child items.
     *
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Sales/_files/quote_with_bundle.php
     */
    public function testSubmit()
    {
        $quote = $this->getQuote('test01');
        $orderId = $this->cartManagement->placeOrder($quote->getId());

        /** @var OrderRepositoryInterface $orderRepository */
        $orderRepository = $this->objectManager->create(OrderRepositoryInterface::class);
        $order = $orderRepository->get($orderId);

        $orderItems = $order->getItems();
        self::assertCount(3, $orderItems);
        foreach ($orderItems as $orderItem) {
            if ($orderItem->getProductType() == Type::TYPE_SIMPLE) {
                self::assertNotEmpty($orderItem->getParentItem(), 'Parent is not set for child product');
                self::assertNotEmpty($orderItem->getParentItemId(), 'Parent is not set for child product');
            }
        }
    }

    /**
     * Tries to create order with product that has child items and one of them was deleted.
     *
     * @magentoAppArea adminhtml
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Sales/_files/quote_with_bundle.php
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Some of the products below do not have all the required options.
     */
    public function testSubmitWithDeletedItem()
    {
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $product = $productRepository->get('simple-2');
        $productRepository->delete($product);
        $quote = $this->getQuote('test01');

        $this->cartManagement->placeOrder($quote->getId());
    }

    /**
     * Tries to create order with item of stock during checkout.
     *
     * @magentoDataFixture Magento/Sales/_files/quote.php
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Some of the products are out of stock.
     * @magentoDbIsolation enabled
     */
    public function testSubmitWithItemOutOfStock()
    {
        $this->makeProductOutOfStock('simple');
        $quote = $this->getQuote('test01');
        $this->cartManagement->placeOrder($quote->getId());
    }

    /**
     * Tries to create an order using quote with empty customer email.
     *
     * Order should not start placing if order validation is failed.
     *
     * @magentoDataFixture Magento/Quote/Fixtures/quote_without_customer_email.php
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Email has a wrong format
     */
    public function testSubmitWithEmptyCustomerEmail()
    {
        $quote = $this->getQuote('test01');
        $orderManagement = $this->createMock(OrderManagementInterface::class);
        $orderManagement->expects($this->never())
            ->method('place');
        $cartManagement = $this->objectManager->create(
            CartManagementInterface::class,
            ['orderManagement' => $orderManagement]
        );

        try {
            $cartManagement->placeOrder($quote->getId());
        } catch (ExpectationFailedException $e) {
            $this->fail('Place order method was not expected to be called if order validation is failed');
        }
    }

    /**
     * Gets quote by reserved order ID.
     *
     * @param string $reservedOrderId
     * @return Quote
     */
    private function getQuote(string $reservedOrderId): Quote
    {
        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $this->objectManager->get(SearchCriteriaBuilder::class);
        $searchCriteria = $searchCriteriaBuilder->addFilter('reserved_order_id', $reservedOrderId)
            ->create();

        /** @var CartRepositoryInterface $quoteRepository */
        $quoteRepository = $this->objectManager->get(CartRepositoryInterface::class);
        $items = $quoteRepository->getList($searchCriteria)
            ->getItems();

        return array_pop($items);
    }

    /**
     * Makes provided product as out of stock.
     *
     * @param string $sku
     * @return void
     */
    private function makeProductOutOfStock(string $sku)
    {
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $product = $productRepository->get($sku);
        $extensionAttributes = $product->getExtensionAttributes();
        $stockItem = $extensionAttributes->getStockItem();
        $stockItem->setIsInStock(false);
        $productRepository->save($product);
    }
}
