<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Weee\Plugin\ConfigurableProduct\Pricing;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Pricing\Price\FinalPrice as CatalogFinalPrice;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Catalog\Test\Fixture\ProductStock as ProductStockFixture;
use Magento\ConfigurableProduct\Pricing\Price\FinalPriceResolver as ConfigurableFinalPriceResolver;
use Magento\ConfigurableProduct\Test\Fixture\Attribute as ConfigurableAttributeFixture;
use Magento\ConfigurableProduct\Test\Fixture\Product as ConfigurableProductFixture;
use Magento\Indexer\Test\Fixture\Indexer;
use Magento\Tax\Test\Fixture\ProductTaxClass;
use Magento\Tax\Test\Fixture\TaxRate;
use Magento\Tax\Test\Fixture\TaxRule;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\TestCase\AbstractController;
use Magento\Weee\Test\Fixture\Attribute as WeeeAttributeFixture;

/**
 * Integration test for Weee FinalPriceResolver plugin
 *
 * Tests that the plugin correctly handles price resolution for configurable products
 * when FPT is enabled with tax settings to prevent double tax application.
 *
 * @magentoAppArea frontend
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FinalPriceResolverTest extends AbstractController
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ConfigurableFinalPriceResolver
     */
    private $finalPriceResolver;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->productRepository = $this->_objectManager->get(ProductRepositoryInterface::class);
        $this->finalPriceResolver = $this->_objectManager->get(ConfigurableFinalPriceResolver::class);
    }

    /**
     * Test that FPT price is correctly resolved without double tax application
     *
     * Preconditions:
     * - Catalog Prices: "Excl. tax"
     * - Display Product Prices In Catalog: "Incl. tax"
     * - FPT is enabled with "Including FPT and FPT description" display
     * - Tax rate: 19%
     *
     * Expected behavior:
     * - Base price should not have tax applied twice
     * - WEEE should be included when display setting requires it
     * - Tax should only be applied once during final rendering
     */
    #[
        Config('tax/calculation/price_includes_tax', 0, 'store', 'default'),
        Config('tax/display/type', 2, 'store', 'default'),
        Config('tax/weee/enable', 1, 'store', 'default'),
        Config('tax/weee/display', 1, 'store', 'default'),
        DataFixture(ProductTaxClass::class, ['class_name' => 'Product Tax Class'], 'product_tax_class'),
        DataFixture(
            TaxRate::class,
            [
                'tax_country_id' => 'US',
                'tax_region_id' => 0,
                'tax_postcode' => '*',
                'code' => 'US-Tax-Rate-19',
                'rate' => 19
            ],
            'tax_rate'
        ),
        DataFixture(
            TaxRule::class,
            [
                'customer_tax_class_ids' => [3],
                'product_tax_class_ids' => ['$product_tax_class.id$'],
                'tax_rate_ids' => ['$tax_rate.id$'],
                'code' => 'Test Tax Rule',
                'priority' => 0
            ],
            'tax_rule'
        ),
        DataFixture(WeeeAttributeFixture::class, ['attribute_code' => 'test_fpt'], 'fpt_attr'),
        DataFixture(
            ConfigurableAttributeFixture::class,
            ['attribute_code' => 'test_configurable'],
            'configurable_attr'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'sku' => 'simple-option-1',
                'price' => 100.00,
                'tax_class_id' => '$product_tax_class.id$',
            ],
            'simple1'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'sku' => 'simple-option-2',
                'price' => 100.00,
                'tax_class_id' => '$product_tax_class.id$',
            ],
            'simple2'
        ),
        DataFixture(
            ConfigurableProductFixture::class,
            [
                'sku' => 'configurable-with-fpt',
                'tax_class_id' => '$product_tax_class.id$',
                '_options' => ['$configurable_attr$'],
                '_links' => ['$simple1$', '$simple2$']
            ],
            'configurable'
        )
    ]
    public function testResolvePriceWithFptAndTaxExclIncl(): void
    {
        $product = $this->productRepository->get('simple-option-1', false, null, true);

        // Get the resolved price from FinalPriceResolver
        $resolvedPrice = $this->finalPriceResolver->resolvePrice($product);

        // Expected: Base price (100)
        // The plugin should return price excluding tax adjustment to prevent double taxation
        $this->assertEqualsWithDelta(
            100.0,
            $resolvedPrice,
            0.01,
            'Resolved price should be base price without tax to avoid double tax application'
        );

        // Verify that when getting the amount with tax, it's only applied once
        $finalPrice = $product->getPriceInfo()->getPrice(CatalogFinalPrice::PRICE_CODE);
        $amountWithTax = $finalPrice->getAmount()->getValue();

        // Expected: Base price (100) + tax (19%) = 119.00
        $this->assertEqualsWithDelta(
            119.0,
            $amountWithTax,
            0.01,
            'Final price with tax should only have tax applied once, not twice'
        );
    }

    /**
     * Test that price resolution works correctly when FPT display is disabled
     */
    #[
        Config('tax/calculation/price_includes_tax', 0, 'store', 'default'),
        Config('tax/display/type', 2, 'store', 'default'),
        Config('tax/weee/enable', 1, 'store', 'default'),
        Config('tax/weee/display', 0, 'store', 'default'),
        DataFixture(ProductTaxClass::class, ['class_name' => 'Product Tax Class'], 'product_tax_class'),
        DataFixture(
            TaxRate::class,
            ['tax_country_id' => 'US', 'tax_region_id' => 0, 'tax_postcode' => '*', 'rate' => 19],
            'tax_rate'
        ),
        DataFixture(
            TaxRule::class,
            [
                'customer_tax_class_ids' => [3],
                'product_tax_class_ids' => ['$product_tax_class.id$'],
                'tax_rate_ids' => ['$tax_rate.id$']
            ]
        ),
        DataFixture(ConfigurableAttributeFixture::class, ['attribute_code' => 'test_configurable'], 'cfg_attr'),
        DataFixture(
            ProductFixture::class,
            ['sku' => 's1', 'price' => 100, 'tax_class_id' => '$product_tax_class.id$'],
            's1'
        ),
        DataFixture(
            ProductFixture::class,
            ['sku' => 's2', 'price' => 100, 'tax_class_id' => '$product_tax_class.id$'],
            's2'
        ),
        DataFixture(
            ConfigurableProductFixture::class,
            [
                'sku' => 'cfg-test',
                'tax_class_id' => '$product_tax_class.id$',
                '_options' => ['$cfg_attr$'],
                '_links' => ['$s1$', '$s2$']
            ],
            'cfg'
        )
    ]
    public function testResolvePriceWithFptDisabledDisplay(): void
    {
        $product = $this->productRepository->get('s1', false, null, true);

        // Get the resolved price from FinalPriceResolver
        $resolvedPrice = $this->finalPriceResolver->resolvePrice($product);

        // Expected: Base price (100) without WEEE or tax at resolution stage
        $this->assertEqualsWithDelta(
            100.0,
            $resolvedPrice,
            0.01,
            'Resolved price should be base price when FPT display is disabled'
        );
    }

    /**
     * Test price resolution with FPT in "Including FPT and description" mode
     */
    #[
        Config('tax/calculation/price_includes_tax', 0, 'store', 'default'),
        Config('tax/display/type', 2, 'store', 'default'),
        Config('tax/weee/enable', 1, 'store', 'default'),
        Config('tax/weee/display', 2, 'store', 'default'),
        DataFixture(ProductTaxClass::class, ['class_name' => 'Product Tax Class'], 'product_tax_class'),
        DataFixture(TaxRate::class, ['tax_country_id' => 'US', 'rate' => 19], 'tax_rate'),
        DataFixture(
            TaxRule::class,
            [
                'customer_tax_class_ids' => [3],
                'product_tax_class_ids' => ['$product_tax_class.id$'],
                'tax_rate_ids' => ['$tax_rate.id$']
            ]
        ),
        DataFixture(ConfigurableAttributeFixture::class, ['attribute_code' => 'test_configurable'], 'cfg_attr'),
        DataFixture(
            ProductFixture::class,
            ['sku' => 'simple-1', 'price' => 100, 'tax_class_id' => '$product_tax_class.id$'],
            's1'
        ),
        DataFixture(
            ProductFixture::class,
            ['sku' => 'simple-2', 'price' => 100, 'tax_class_id' => '$product_tax_class.id$'],
            's2'
        ),
        DataFixture(
            ConfigurableProductFixture::class,
            [
                'sku' => 'cfg-fpt-desc',
                'tax_class_id' => '$product_tax_class.id$',
                '_options' => ['$cfg_attr$'],
                '_links' => ['$s1$', '$s2$']
            ],
            'cfg'
        )
    ]
    public function testResolvePriceWithFptInclDesc(): void
    {
        $product = $this->productRepository->get('simple-1', false, null, true);

        // Get the resolved price from FinalPriceResolver
        $resolvedPrice = $this->finalPriceResolver->resolvePrice($product);

        // The plugin should use getAmount()->getValue(Adjustment::ADJUSTMENT_CODE)
        // which excludes tax to prevent double taxation
        $this->assertEqualsWithDelta(
            100.0,
            $resolvedPrice,
            0.01,
            'Resolved price should exclude tax adjustment'
        );

        // Verify final price doesn't have double tax
        $finalPrice = $product->getPriceInfo()->getPrice(CatalogFinalPrice::PRICE_CODE);
        $amountWithTax = $finalPrice->getAmount()->getValue();

        $this->assertEqualsWithDelta(
            119.0,
            $amountWithTax,
            0.01,
            'Final price should have tax applied only once'
        );
    }

    /**
     * Test with catalog prices including tax
     */
    #[
        Config('tax/calculation/price_includes_tax', 1, 'store', 'default'),
        Config('tax/display/type', 2, 'store', 'default'),
        Config('tax/weee/enable', 1, 'store', 'default'),
        Config('tax/weee/display', 1, 'store', 'default'),
        DataFixture(ProductTaxClass::class, ['class_name' => 'Product Tax Class'], 'product_tax_class'),
        DataFixture(TaxRate::class, ['tax_country_id' => 'US', 'rate' => 19], 'tax_rate'),
        DataFixture(
            TaxRule::class,
            [
                'customer_tax_class_ids' => [3],
                'product_tax_class_ids' => ['$product_tax_class.id$'],
                'tax_rate_ids' => ['$tax_rate.id$']
            ]
        ),
        DataFixture(ConfigurableAttributeFixture::class, ['attribute_code' => 'test_configurable'], 'cfg_attr'),
        DataFixture(
            ProductFixture::class,
            ['sku' => 'simple-incl-1', 'price' => 100, 'tax_class_id' => '$product_tax_class.id$'],
            's1'
        ),
        DataFixture(
            ProductFixture::class,
            ['sku' => 'simple-incl-2', 'price' => 100, 'tax_class_id' => '$product_tax_class.id$'],
            's2'
        ),
        DataFixture(
            ConfigurableProductFixture::class,
            [
                'sku' => 'cfg-incl-tax',
                'tax_class_id' => '$product_tax_class.id$',
                '_options' => ['$cfg_attr$'],
                '_links' => ['$s1$', '$s2$']
            ],
            'cfg'
        )
    ]
    public function testResolvePriceWithCatalogPriceIncludingTax(): void
    {
        $product = $this->productRepository->get('simple-incl-1', false, null, true);

        // Get the resolved price from FinalPriceResolver
        $resolvedPrice = $this->finalPriceResolver->resolvePrice($product);

        // When prices include tax, the base price already has tax
        // The plugin should still exclude tax adjustment to avoid issues
        $this->assertGreaterThan(
            0,
            $resolvedPrice,
            'Resolved price should be greater than 0'
        );

        // Verify that tax is not applied multiple times
        $finalPrice = $product->getPriceInfo()->getPrice(CatalogFinalPrice::PRICE_CODE);
        $amountWithTax = $finalPrice->getAmount()->getValue();

        // Should not have tax compounded
        $this->assertLessThan(
            150.0,
            $amountWithTax,
            'Final price should not have compounded tax'
        );
    }

    /**
     * Test frontend configurable product page displays correct price without double taxation
     *
     * @magentoDbIsolation disabled
     */
    #[
        Config('tax/calculation/price_includes_tax', 0, 'store', 'default'),
        Config('tax/display/type', 2, 'store', 'default'),
        Config('tax/weee/enable', 1, 'store', 'default'),
        Config('tax/weee/display', 1, 'store', 'default'),
        DataFixture(ProductTaxClass::class, ['class_name' => 'Product Tax Class'], 'product_tax_class'),
        DataFixture(
            TaxRate::class,
            ['tax_country_id' => 'US', 'tax_region_id' => 0, 'tax_postcode' => '*', 'rate' => 19],
            'tax_rate'
        ),
        DataFixture(
            TaxRule::class,
            [
                'customer_tax_class_ids' => [3],
                'product_tax_class_ids' => ['$product_tax_class.id$'],
                'tax_rate_ids' => ['$tax_rate.id$']
            ]
        ),
        DataFixture(ConfigurableAttributeFixture::class, ['attribute_code' => 'test_configurable'], 'cfg_attr'),
        DataFixture(
            ProductFixture::class,
            ['sku' => 'simple-frontend-1', 'price' => 100.00, 'tax_class_id' => '$product_tax_class.id$'],
            's1'
        ),
        DataFixture(
            ProductStockFixture::class,
            ['prod_id' => '$s1.id$', 'prod_qty' => 100, 'is_in_stock' => 1]
        ),
        DataFixture(
            ProductFixture::class,
            ['sku' => 'simple-frontend-2', 'price' => 100.00, 'tax_class_id' => '$product_tax_class.id$'],
            's2'
        ),
        DataFixture(
            ProductStockFixture::class,
            ['prod_id' => '$s2.id$', 'prod_qty' => 100, 'is_in_stock' => 1]
        ),
        DataFixture(
            ConfigurableProductFixture::class,
            [
                'sku' => 'configurable-frontend',
                'name' => 'Configurable Product Frontend',
                'tax_class_id' => '$product_tax_class.id$',
                '_options' => ['$cfg_attr$'],
                '_links' => ['$s1$', '$s2$']
            ],
            'configurable'
        ),
        DataFixture(Indexer::class, ['indexer_id' => 'cataloginventory_stock']),
        DataFixture(Indexer::class, ['indexer_id' => 'catalog_product_price'])
    ]
    public function testFrontendConfigurableProductPageDisplaysCorrectPrice(): void
    {
        // Get the product
        $product = $this->productRepository->get('configurable-frontend');

        // Dispatch to product page
        $this->dispatch(sprintf('catalog/product/view/id/%s', $product->getId()));

        // Get response body
        $responseBody = $this->getResponse()->getBody();

        // Assert product name is in response
        $this->assertStringContainsString('Configurable Product Frontend', $responseBody);

        // Assert the correct price is displayed ($119.00)
        // The price should be shown with tax included
        $this->assertStringContainsString(
            '$119.00',
            $responseBody,
            'Price should be $119.00 (base $100 + 19% tax)'
        );

        // Assert the WRONG price (double tax) is NOT displayed
        $this->assertStringNotContainsString(
            '$141.61',
            $responseBody,
            'Price should NOT be $141.61 (double tax bug)'
        );
    }
}
