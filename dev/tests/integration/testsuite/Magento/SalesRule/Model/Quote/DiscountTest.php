<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Model\Quote;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;
use Magento\SalesRule\Test\Fixture\ProductCondition as ProductConditionFixture;
use Magento\SalesRule\Test\Fixture\Rule as RuleFixture;
use Magento\TestFramework\Fixture\AppIsolation;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test discount totals calculation model
 */
class DiscountTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
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
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->objectManager = Bootstrap::getObjectManager();
        $this->criteriaBuilder = $this->objectManager->get(SearchCriteriaBuilder::class);
        $this->quoteRepository = $this->objectManager->get(CartRepositoryInterface::class);
    }

    /**
     * @magentoDataFixture Magento/Checkout/_files/quote_with_bundle_product_with_dynamic_price.php
     * @dataProvider bundleProductWithDynamicPriceAndCartPriceRuleDataProvider
     * @param string $coupon
     * @param array $discounts
     * @param float $totalDiscount
     * @return void
     */
    #[
        AppIsolation(true),
        DataFixture(
            ProductConditionFixture::class,
            ['attribute' => 'sku', 'value' => 'bundle_product_with_dynamic_price'],
            'cond1'
        ),
        DataFixture(
            ProductConditionFixture::class,
            ['attribute' => 'sku', 'value' => 'simple1'],
            'cond2'
        ),
        DataFixture(
            ProductConditionFixture::class,
            ['attribute' => 'sku', 'value' => 'simple2'],
            'cond3'
        ),
        DataFixture(
            RuleFixture::class,
            ['coupon_code' => 'bundle_cc', 'discount_amount' => 50, 'actions' => ['$cond1$']],
            'rule1'
        ),
        DataFixture(
            RuleFixture::class,
            ['coupon_code' => 'simple1_cc', 'discount_amount' => 50, 'actions' => ['$cond2$']],
            'rule2'
        ),
        DataFixture(
            RuleFixture::class,
            ['coupon_code' => 'simple2_cc', 'discount_amount' => 50, 'actions' => ['$cond3$']],
            'rule3'
        ),
    ]
    public function testBundleProductWithDynamicPriceAndCartPriceRule(
        string $coupon,
        array $discounts,
        float $totalDiscount
    ): void {
        $quote = $this->getQuote('quote_with_bundle_product_with_dynamic_price');
        $quote->setCouponCode($coupon);
        $quote->collectTotals();
        $this->quoteRepository->save($quote);
        $this->assertEquals(21.98, $quote->getBaseSubtotal());
        $this->assertEquals($totalDiscount, $quote->getShippingAddress()->getDiscountAmount());
        $items = $quote->getAllItems();
        $this->assertCount(3, $items);
        /** @var Item $item*/
        $item = array_shift($items);
        $this->assertEquals('bundle_product_with_dynamic_price-simple1-simple2', $item->getSku());
        $this->assertEquals($discounts[$item->getSku()], $item->getDiscountAmount());
        $item = array_shift($items);
        $this->assertEquals('simple1', $item->getSku());
        $this->assertEquals(5.99, $item->getPrice());
        $this->assertEquals($discounts[$item->getSku()], $item->getDiscountAmount());
        $item = array_shift($items);
        $this->assertEquals('simple2', $item->getSku());
        $this->assertEquals(15.99, $item->getPrice());
        $this->assertEquals($discounts[$item->getSku()], $item->getDiscountAmount());
    }

    /**
     * @return array
     */
    public function bundleProductWithDynamicPriceAndCartPriceRuleDataProvider(): array
    {
        return [
            [
                'bundle_cc',
                [
                    'bundle_product_with_dynamic_price-simple1-simple2' => 0,
                    'simple1' => 3,
                    'simple2' => 7.99,
                ],
                -10.99
            ],
            [
                'simple1_cc',
                [
                    'bundle_product_with_dynamic_price-simple1-simple2' => 0,
                    'simple1' => 3,
                    'simple2' => 0,
                ],
                -3
            ],
            [
                'simple2_cc',
                [
                    'bundle_product_with_dynamic_price-simple1-simple2' => 0,
                    'simple1' => 0,
                    'simple2' => 8,
                ],
                -8
            ]
        ];
    }

    /**
     * @param string $reservedOrderId
     * @return Quote
     */
    private function getQuote(string $reservedOrderId): Quote
    {
        $searchCriteria = $this->criteriaBuilder->addFilter('reserved_order_id', $reservedOrderId)
            ->create();
        $carts = $this->quoteRepository->getList($searchCriteria)
            ->getItems();
        return array_shift($carts);
    }
}
