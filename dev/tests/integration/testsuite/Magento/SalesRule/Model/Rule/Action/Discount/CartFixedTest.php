<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Model\Rule\Action\Discount;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Api\GuestCartItemRepositoryInterface;
use Magento\Quote\Api\GuestCartManagementInterface;
use Magento\Quote\Api\GuestCartTotalRepositoryInterface;
use Magento\Quote\Api\GuestCouponManagementInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Tests for Magento\SalesRule\Model\Rule\Action\Discount\CartFixed.
 *
 * @magentoAppArea frontend
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CartFixedTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var GuestCartManagementInterface
     */
    private $cartManagement;

    /**
     * @var GuestCartItemRepositoryInterface
     */
    private $cartItemRepository;

    /**
     * @var GuestCouponManagementInterface
     */
    private $couponManagement;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var SearchCriteriaBuilder
     */
    private $criteriaBuilder;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->cartManagement = $objectManager->create(GuestCartManagementInterface::class);
        $this->couponManagement = $objectManager->create(GuestCouponManagementInterface::class);
        $this->cartItemRepository = $objectManager->create(GuestCartItemRepositoryInterface::class);
        $this->criteriaBuilder = $objectManager->get(SearchCriteriaBuilder::class);
        $this->quoteRepository = $objectManager->get(CartRepositoryInterface::class);
        $this->objectManager = $objectManager;
    }

    /**
     * Applies fixed discount amount on whole cart.
     *
     * @param array $productPrices
     * @return void
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/SalesRule/_files/coupon_cart_fixed_discount.php
     * @dataProvider applyFixedDiscountDataProvider
     */
    public function testApplyFixedDiscount(array $productPrices): void
    {
        $expectedDiscount = '-15.00';
        $couponCode =  'CART_FIXED_DISCOUNT_15';
        $cartId = $this->cartManagement->createEmptyCart();

        foreach ($productPrices as $price) {
            $product = $this->createProduct($price);

            /** @var CartItemInterface $quoteItem */
            $quoteItem = Bootstrap::getObjectManager()->create(CartItemInterface::class);
            $quoteItem->setQuoteId($cartId);
            $quoteItem->setProduct($product);
            $quoteItem->setQty(1);
            $this->cartItemRepository->save($quoteItem);
        }

        $this->couponManagement->set($cartId, $couponCode);

        /** @var GuestCartTotalRepositoryInterface $cartTotalRepository */
        $cartTotalRepository = Bootstrap::getObjectManager()->get(GuestCartTotalRepositoryInterface::class);
        $total = $cartTotalRepository->get($cartId);

        $this->assertEquals($expectedDiscount, $total->getBaseDiscountAmount());
    }

    /**
     * Applies fixed discount amount on whole cart and created order with it
     *
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoConfigFixture default_store carriers/freeshipping/active 1
     * @magentoDataFixture Magento/Sales/_files/quote.php
     * @magentoDataFixture Magento/SalesRule/_files/coupon_cart_fixed_subtotal_with_discount.php
     */
    public function testOrderWithFixedDiscount(): void
    {
        $expectedGrandTotal = 5;

        $quote = $this->getQuote();
        $quote->getShippingAddress()
            ->setShippingMethod('freeshipping_freeshipping')
            ->setCollectShippingRates(true);
        $quote->setCouponCode('CART_FIXED_DISCOUNT_15');
        $quote->collectTotals();
        $this->quoteRepository->save($quote);
        $this->assertEquals($expectedGrandTotal, $quote->getGrandTotal());

        /** @var \Magento\Quote\Model\QuoteIdMask $quoteIdMask */
        $quoteIdMask = $this->objectManager->create(QuoteIdMask::class);
        $quoteIdMask->load($quote->getId(), 'quote_id');
        Bootstrap::getInstance()->reinitialize();
        $cartManagement = Bootstrap::getObjectManager()->create(GuestCartManagementInterface::class);
        $cartManagement->placeOrder($quoteIdMask->getMaskedId());
        $order = $this->getOrder('test01');
        $this->assertEquals($expectedGrandTotal, $order->getGrandTotal());
    }

    /**
     * Applies fixed discount amount on whole cart and quote and checks the quote model for item discounts
     *
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     * @magentoConfigFixture default_store carriers/freeshipping/active 1
     * @magentoDataFixture Magento/Sales/_files/quote.php
     * @magentoDataFixture Magento/SalesRule/_files/coupon_cart_fixed_subtotal_with_discount.php
     */
    public function testDiscountsOnQuoteWithFixedDiscount(): void
    {
        $quote = $this->getQuote();
        $quote->getShippingAddress()
            ->setShippingMethod('freeshipping_freeshipping')
            ->setCollectShippingRates(true);
        $quote->setCouponCode('CART_FIXED_DISCOUNT_15');
        $quote->collectTotals();
        $this->quoteRepository->save($quote);
        /** @var CartItemInterface $item */
        $item = $quote->getItems()[0];
        $quoteItemDiscounts = $item->getExtensionAttributes()->getDiscounts();
        $this->assertArrayHasKey('0', $quoteItemDiscounts);
        $discountData = $quoteItemDiscounts[0]->getDiscountData();
        $ruleLabel = $quoteItemDiscounts[0]->getRuleLabel();
        $this->assertEquals(5, $discountData->getAmount());
        $this->assertEquals(5, $discountData->getBaseAmount());
        $this->assertEquals(5, $discountData->getOriginalAmount());
        $this->assertEquals(10, $discountData->getBaseOriginalAmount());
        $this->assertEquals('TestRule_Coupon', $ruleLabel);

        $quoteAddressItemDiscount = $quote->getShippingAddressesItems()[0]->getExtensionAttributes()->getDiscounts();
        $this->assertArrayHasKey('0', $quoteAddressItemDiscount);
        $discountData = $quoteAddressItemDiscount[0]->getDiscountData();
        $ruleLabel = $quoteAddressItemDiscount[0]->getRuleLabel();
        $this->assertEquals(5, $discountData->getAmount());
        $this->assertEquals(5, $discountData->getBaseAmount());
        $this->assertEquals(5, $discountData->getOriginalAmount());
        $this->assertEquals(10, $discountData->getBaseOriginalAmount());
        $this->assertEquals('TestRule_Coupon', $ruleLabel);
    }

    /**
     * Load cart from fixture.
     *
     * @return Quote
     */
    private function getQuote(): Quote
    {
        $searchCriteria = $this->criteriaBuilder->addFilter('reserved_order_id', 'test01')->create();
        $carts = $this->quoteRepository->getList($searchCriteria)
            ->getItems();
        if (!$carts) {
            throw new \RuntimeException('Cart from fixture not found');
        }

        return array_shift($carts);
    }

    /**
     * @return array
     */
    public function applyFixedDiscountDataProvider(): array
    {
        return [
            'prices when discount had wrong value 15.01' => [[22, 14, 43, 7.50, 0.00]],
            'prices when discount had wrong value 14.99' => [[47, 33, 9.50, 42, 0.00]],
        ];
    }

    /**
     * Tests that coupon with wildcard symbols in code can be successfully applied.
     *
     * @magentoDataFixture Magento/SalesRule/_files/coupon_code_with_wildcard.php
     */
    public function testCouponCodeWithWildcard()
    {
        $expectedDiscount = '-5.00';
        $couponCode =  '2?ds5!2d';
        $cartId = $this->cartManagement->createEmptyCart();
        $productPrice = 10;

        $product = $this->createProduct($productPrice);

        /** @var CartItemInterface $quoteItem */
        $quoteItem = Bootstrap::getObjectManager()->create(CartItemInterface::class);
        $quoteItem->setQuoteId($cartId);
        $quoteItem->setProduct($product);
        $quoteItem->setQty(1);
        $this->cartItemRepository->save($quoteItem);

        $this->couponManagement->set($cartId, $couponCode);

        /** @var GuestCartTotalRepositoryInterface $cartTotalRepository */
        $cartTotalRepository = Bootstrap::getObjectManager()->get(GuestCartTotalRepositoryInterface::class);
        $total = $cartTotalRepository->get($cartId);

        $this->assertEquals($expectedDiscount, $total->getBaseDiscountAmount());
    }

    /**
     * Returns simple product with given price.
     *
     * @param float $price
     * @return ProductInterface
     */
    private function createProduct(float $price): ProductInterface
    {
        $name = 'simple-' . $price;
        $productRepository = Bootstrap::getObjectManager()->get(ProductRepository::class);
        $product = Bootstrap::getObjectManager()->create(Product::class);
        $product->setTypeId('simple')
            ->setAttributeSetId($product->getDefaultAttributeSetId())
            ->setWebsiteIds([1])
            ->setName($name)
            ->setSku(uniqid($name))
            ->setPrice($price)
            ->setMetaTitle('meta title')
            ->setMetaKeyword('meta keyword')
            ->setMetaDescription('meta description')
            ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
            ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
            ->setStockData(['qty' => 1, 'is_in_stock' => 1])
            ->setWeight(1);

        return $productRepository->save($product);
    }

    /**
     * Gets order entity by increment id.
     *
     * @param string $incrementId
     * @return OrderInterface
     */
    private function getOrder(string $incrementId): OrderInterface
    {
        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $this->objectManager->get(SearchCriteriaBuilder::class);
        $searchCriteria = $searchCriteriaBuilder->addFilter('increment_id', $incrementId)
            ->create();

        /** @var OrderRepositoryInterface $repository */
        $repository = $this->objectManager->get(OrderRepositoryInterface::class);
        $items = $repository->getList($searchCriteria)
            ->getItems();

        return array_pop($items);
    }
}
