<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Bundle;

use Magento\Bundle\Test\Fixture\AddProductToCart as AddBundleProductToCart;
use Magento\Bundle\Test\Fixture\Link as BundleSelectionFixture;
use Magento\Bundle\Test\Fixture\Option as BundleOptionFixture;
use Magento\Bundle\Test\Fixture\Product as BundleProductFixture;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;
use Magento\Quote\Test\Fixture\GuestCart as GuestCartFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\Bundle\Model\Product\Price;
use Magento\SalesRule\Model\Rule as SalesRule;
use Magento\SalesRule\Test\Fixture\AddressCondition as AddressConditionFixture;
use Magento\SalesRule\Test\Fixture\Rule as SalesRuleFixture;

class BundleProductDynamicPriceTest extends GraphQlAbstract
{
    /**
     * @var QuoteIdToMaskedQuoteIdInterface
     */
    private $quoteIdToMaskedId;

    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->quoteIdToMaskedId = $objectManager->get(QuoteIdToMaskedQuoteIdInterface::class);
        $this->fixtures = DataFixtureStorageManager::getStorage();
    }

    #[
        DataFixture(
            AddressConditionFixture::class,
            ['attribute' => 'base_subtotal', 'operator' => '>=', 'value' => 0],
            'condition'
        ),
        DataFixture(
            SalesRuleFixture::class,
            [
                'store_labels' => [1 => 'Coupon1'],
                'simple_action' => SalesRule::BY_PERCENT_ACTION,
                'uses_per_customer' => 1,
                'discount_amount' => 10,
                'stop_rules_processing' => false,
                'conditions' => ['$condition$']
            ]
        ),
        DataFixture(ProductFixture::class, ['sku' => 'simple1', 'price' => 100], as:'p1'),
        DataFixture(ProductFixture::class, ['sku' => 'simple2', 'price' => 200], as:'p2'),
        DataFixture(BundleSelectionFixture::class, ['sku' => '$p1.sku$'], as:'link1'),
        DataFixture(BundleSelectionFixture::class, ['sku' => '$p2.sku$'], as:'link2'),
        DataFixture(BundleOptionFixture::class, ['title' => 'Checkbox Options', 'type' => 'checkbox',
            'required' => 1,'product_links' => ['$link1$', '$link2$']], 'opt1'),
        DataFixture(BundleOptionFixture::class, ['title' => 'Checkbox Options', 'type' => 'checkbox',
            'required' => 1,'product_links' => ['$link1$', '$link2$']], 'opt2'),
        DataFixture(
            BundleProductFixture::class,
            [
                'sku' => 'bundle-product-multiselect-checkbox-options',
                'price_type' => Price::PRICE_TYPE_DYNAMIC,
                '_options' => [
                    '$opt1$',
                    '$opt2$'
                ]
            ],
            as:'bp1'
        ),
        DataFixture(GuestCartFixture::class, as: 'guestCart'),
        DataFixture(
            AddBundleProductToCart::class,
            [
                'cart_id' => '$guestCart.id$',
                'product_id' => '$bp1.id$',
                'selections' => [['$p1.id$'], ['$p2.id$']],
                'qty' => 1
            ]
        )
    ]
    public function testCartBundleProductPriceDetailsWithDynamicPriceAndDiscount()
    {
        $guestCart = $this->fixtures->get('guestCart');
        $guestQuoteMaskedId = $this->quoteIdToMaskedId->execute((int)$guestCart->getId());
        $cartQuery = $this->getGuestCartQuery($guestQuoteMaskedId);
        $cartResponse = $this->graphQlMutation($cartQuery);
        $dynamicPrice = $cartResponse['cart']['itemsV2']['items'][0]['product']['dynamic_price'];
        self::assertTrue($dynamicPrice);
        $productPriceDetails = $cartResponse['cart']['itemsV2']['items'][0]['product']['price_details'];
        self::assertArrayHasKey('main_price', $productPriceDetails);
        self::assertArrayHasKey('main_final_price', $productPriceDetails);
        self::assertArrayHasKey('discount_percentage', $productPriceDetails);
        self::assertEquals(0, $productPriceDetails['main_price']);
        self::assertEquals(300, $productPriceDetails['main_final_price']);
        self::assertEquals(10, $productPriceDetails['discount_percentage']);
    }

    #[
        DataFixture(ProductFixture::class, ['sku' => 'simple1', 'price' => 100], as:'p1'),
        DataFixture(ProductFixture::class, ['sku' => 'simple2', 'price' => 200], as:'p2'),
        DataFixture(BundleSelectionFixture::class, ['sku' => '$p1.sku$'], as:'link1'),
        DataFixture(BundleSelectionFixture::class, ['sku' => '$p2.sku$'], as:'link2'),
        DataFixture(BundleOptionFixture::class, ['title' => 'Checkbox Options', 'type' => 'checkbox',
            'required' => 1,'product_links' => ['$link1$', '$link2$']], 'opt1'),
        DataFixture(BundleOptionFixture::class, ['title' => 'Checkbox Options', 'type' => 'checkbox',
            'required' => 1,'product_links' => ['$link1$', '$link2$']], 'opt2'),
        DataFixture(
            BundleProductFixture::class,
            [
                'sku' => 'bundle-product-multiselect-checkbox-options',
                'price_type' => Price::PRICE_TYPE_DYNAMIC,
                '_options' => [
                    '$opt1$',
                    '$opt2$'
                ]
            ],
            as:'bp1'
        ),
        DataFixture(GuestCartFixture::class, as: 'guestCart'),
        DataFixture(
            AddBundleProductToCart::class,
            [
                'cart_id' => '$guestCart.id$',
                'product_id' => '$bp1.id$',
                'selections' => [['$p1.id$'], ['$p2.id$']],
                'qty' => 1
            ]
        )
    ]
    public function testCartBundleProductPriceDetailsWithDynamicPriceNoDiscount()
    {
        $guestCart = $this->fixtures->get('guestCart');
        $guestQuoteMaskedId = $this->quoteIdToMaskedId->execute((int)$guestCart->getId());
        $cartQuery = $this->getGuestCartQuery($guestQuoteMaskedId);
        $cartResponse = $this->graphQlMutation($cartQuery);
        $dynamicPrice = $cartResponse['cart']['itemsV2']['items'][0]['product']['dynamic_price'];
        self::assertTrue($dynamicPrice);
        $productPriceDetails = $cartResponse['cart']['itemsV2']['items'][0]['product']['price_details'];
        self::assertArrayHasKey('main_price', $productPriceDetails);
        self::assertArrayHasKey('main_final_price', $productPriceDetails);
        self::assertArrayHasKey('discount_percentage', $productPriceDetails);
        self::assertEquals(0, $productPriceDetails['main_price']);
        self::assertEquals(300, $productPriceDetails['main_final_price']);
        self::assertEquals(0, $productPriceDetails['discount_percentage']);
    }

    /**
     * Get guest cart query
     *
     * @param string $maskedId
     * @return string
     */
    private function getGuestCartQuery(string $maskedId): string
    {
        return <<<QUERY
{
  cart(cart_id: "{$maskedId}") {
    itemsV2 {
      items {
        product {
          sku
          ... on BundleProduct {
            dynamic_price
            price_view
            price_details {
              main_price
              main_final_price
              discount_percentage
            }
          }
        }
      }
    }
  }
}
QUERY;
    }
}
