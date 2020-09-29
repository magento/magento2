<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Model\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\TierPriceInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Model\Group;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\TestFramework\Catalog\Model\GetCategoryByName;
use Magento\TestFramework\Catalog\Model\Product\Price\GetPriceIndexDataByProductId;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Class to test bundle prices
 *
 * @magentoDbIsolation disabled
 * @magentoAppIsolation enabled
 * @magentoAppArea frontend
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PriceTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var GetPriceIndexDataByProductId */
    private $getPriceIndexDataByProductId;

    /** @var WebsiteRepositoryInterface */
    private $websiteRepository;

    /** @var Price */
    private $priceModel;

    /** @var SerializerInterface */
    private $json;

    /** @var GetCategoryByName */
    private $getCategoryByName;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->priceModel = $this->objectManager->get(Price::class);
        $this->websiteRepository = $this->objectManager->get(WebsiteRepositoryInterface::class);
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $this->productRepository->cleanCache();
        $this->getPriceIndexDataByProductId = $this->objectManager->get(GetPriceIndexDataByProductId::class);
        $this->json = $this->objectManager->get(SerializerInterface::class);
        $this->getCategoryByName = $this->objectManager->get(GetCategoryByName::class);
    }

    /**
     * @magentoDataFixture Magento/Bundle/_files/product_with_tier_pricing.php
     *
     * @return void
     */
    public function testGetTierPrice(): void
    {
        $product = $this->productRepository->get('bundle-product');
        // Note that this is really not the "tier price" but the "tier discount percentage"
        // so it is expected to be increasing instead of decreasing
        $this->assertEquals(8.0, $this->priceModel->getTierPrice(2, $product));
        $this->assertEquals(20.0, $this->priceModel->getTierPrice(3, $product));
        $this->assertEquals(20.0, $this->priceModel->getTierPrice(4, $product));
        $this->assertEquals(30.0, $this->priceModel->getTierPrice(5, $product));
    }

    /**
     * Test calculation final price for bundle product with tire price in simple product
     * @magentoDataFixture Magento/Bundle/_files/product_with_simple_tier_pricing.php
     * @dataProvider getSelectionFinalTotalPriceWithSimpleTierPriceDataProvider
     *
     * @param float $bundleQty
     * @param float $selectionQty
     * @param float $finalPrice
     * @return void
     */
    public function testGetSelectionFinalTotalPriceWithSimpleTierPrice(
        float $bundleQty,
        float $selectionQty,
        float $finalPrice
    ): void {
        $bundleProduct = $this->productRepository->get('bundle-product');
        $simpleProduct = $this->productRepository->get('simple');
        $simpleProduct->setCustomerGroupId(Group::CUST_GROUP_ALL);

        $this->assertEquals(
            $finalPrice,
            $this->priceModel->getSelectionFinalTotalPrice(
                $bundleProduct,
                $simpleProduct,
                $bundleQty,
                $selectionQty,
                false
            ),
            'Tier price calculation for Simple product is wrong'
        );
    }

    /**
     * @return array
     */
    public function getSelectionFinalTotalPriceWithSimpleTierPriceDataProvider(): array
    {
        return [
            [1, 1, 10],
            [2, 1, 8],
            [5, 1, 5],
        ];
    }

    /**
     * Fixed Bundle Product with catalog price rule
     * @magentoDataFixture Magento/Bundle/_files/fixed_bundle_product_without_discounts.php
     * @magentoDataFixture Magento/CatalogRule/_files/rule_apply_as_percentage_of_original_not_logged_user.php
     *
     * @return void
     */
    public function testFixedBundleProductPriceWithCatalogRule(): void
    {
        $this->checkBundlePrices(
            'fixed_bundle_product_without_discounts',
            ['price' => 50, 'final_price' => 45, 'min_price' => 45, 'max_price' => 75, 'tier_price' => null],
            ['simple1' => 55, 'simple2' => 56.25, 'simple3' => 70]
        );
    }

    /**
     * Fixed Bundle Product without discounts
     * @magentoDataFixture Magento/Bundle/_files/fixed_bundle_product_without_discounts.php
     *
     * @return void
     */
    public function testFixedBundleProductPriceWithoutDiscounts(): void
    {
        $this->checkBundlePrices(
            'fixed_bundle_product_without_discounts',
            ['price' => 50, 'final_price' => 50, 'min_price' => 60, 'max_price' => 75, 'tier_price' => null],
            ['simple1' => 60, 'simple2' => 62.5, 'simple3' => 75]
        );
    }

    /**
     * Fixed Bundle Product with special price
     * @magentoDataFixture Magento/Bundle/_files/fixed_bundle_product_with_special_price.php
     *
     * @return void
     */
    public function testFixedBundleProductPriceWithSpecialPrice(): void
    {
        $this->checkBundlePrices(
            'fixed_bundle_product_with_special_price',
            ['price' => 50, 'final_price' => 40, 'min_price' => 48, 'max_price' => 60, 'tier_price' => null],
            ['simple1' => 48, 'simple2' => 50, 'simple3' => 60]
        );
    }

    /**
     * Fixed Bundle Product with tier price
     * @magentoDataFixture Magento/Bundle/_files/fixed_bundle_product_with_tier_price.php
     *
     * @return void
     */
    public function testFixedBundleProductPriceWithTierPrice(): void
    {
        $this->checkBundlePrices(
            'fixed_bundle_product_with_tier_price',
            ['price' => 50, 'final_price' => 50, 'min_price' => 60, 'max_price' => 75, 'tier_price' => 60],
            ['simple1' => 45, 'simple2' => 46.88, 'simple3' => 56.25]
        );
    }

    /**
     * Dynamic Bundle Product without discount + options without discounts
     * @magentoDataFixture Magento/Bundle/_files/dynamic_bundle_product_without_discounts.php
     *
     * @return void
     */
    public function testDynamicBundleProductWithoutDiscountAndOptionsWithoutDiscounts(): void
    {
        $this->checkBundlePrices(
            'dynamic_bundle_product_without_discounts',
            ['price' => 0, 'final_price' => 0, 'min_price' => 10, 'max_price' => 20, 'tier_price' => null],
            ['simple1000' => 10, 'simple1001' => 20]
        );
    }

    /**
     * Dynamic Bundle Product without discount + options with special price
     * @magentoDataFixture Magento/Bundle/_files/dynamic_bundle_product_without_discounts.php
     *
     * @return void
     */
    public function testDynamicBundleProductWithoutDiscountsAndOptionsWithSpecialPrices(): void
    {
        $this->updateProducts($this->specialPricesForOptionsData());
        $this->checkBundlePrices(
            'dynamic_bundle_product_without_discounts',
            ['price' => 0, 'final_price' => 0, 'min_price' => 8, 'max_price' => 15, 'tier_price' => null],
            ['simple1000' => 8, 'simple1001' => 15]
        );
    }

    /**
     * Dynamic Bundle Product without discount + options with tier prices
     * @magentoDataFixture Magento/Bundle/_files/dynamic_bundle_product_without_discounts.php
     *
     * @return void
     */
    public function testDynamicBundleProductWithoutDiscountsAndOptionsWithTierPrices(): void
    {
        $this->updateProducts($this->tierPricesForOptionsData());
        $this->checkBundlePrices(
            'dynamic_bundle_product_without_discounts',
            ['price' => 0, 'final_price' => 0, 'min_price' => 8, 'max_price' => 17, 'tier_price' => null],
            ['simple1000' => 8, 'simple1001' => 17]
        );
    }

    /**
     * Dynamic Bundle Product without discounts + options with catalog rule
     * @magentoDataFixture Magento/Bundle/_files/dynamic_bundle_product_without_discounts.php
     * @magentoDataFixture Magento/CatalogRule/_files/catalog_rule_for_category_999.php
     *
     * @return void
     */
    public function testDynamicBundleProductWithoutDiscountsAndOptionsWithCatalogPriceRule(): void
    {
        $this->checkBundlePrices(
            'dynamic_bundle_product_without_discounts',
            ['price' => 0, 'final_price' => 0, 'min_price' => 7.5, 'max_price' => 15, 'tier_price' => null],
            ['simple1000' => 7.5, 'simple1001' => 15]
        );
    }

    /**
     * Dynamic Bundle Product with tier price + options without discounts
     * @magentoDataFixture Magento/Bundle/_files/dynamic_bundle_product_with_tier_price.php
     *
     * @return void
     */
    public function testDynamicBundleProductWithTierPriceAndOptionsWithoutDiscounts(): void
    {
        $this->checkBundlePrices(
            'dynamic_bundle_product_with_tier_price',
            ['price' => 0,'final_price' => 0, 'min_price' => 10, 'max_price' => 20, 'tier_price' => 10],
            ['simple1000' => 7.5, 'simple1001' => 15]
        );
    }

    /**
     * Dynamic Bundle Product with tier price + options with special prices
     * @magentoDataFixture Magento/Bundle/_files/dynamic_bundle_product_with_tier_price.php
     *
     * @return void
     */
    public function testDynamicBundleProductWithTierPriceAndOptionsWithSpecialPrices(): void
    {
        $this->updateProducts($this->specialPricesForOptionsData());
        $this->checkBundlePrices(
            'dynamic_bundle_product_with_tier_price',
            ['price' => 0, 'final_price' => 0, 'min_price' => 8, 'max_price' => 15, 'tier_price' => 8],
            ['simple1000' => 6, 'simple1001' => 11.25]
        );
    }

    /**
     * Dynamic Bundle Product with tier price + options with tier price
     * @magentoDataFixture Magento/Bundle/_files/dynamic_bundle_product_with_tier_price.php
     *
     * @return void
     */
    public function testDynamicBundleProductWithTierPriceAndOptionsWithTierPrices(): void
    {
        $this->updateProducts($this->tierPricesForOptionsData());
        $this->checkBundlePrices(
            'dynamic_bundle_product_with_tier_price',
            ['price' => 0, 'final_price' => 0, 'min_price' => 8, 'max_price' => 17, 'tier_price' => 8],
            ['simple1000' => 6, 'simple1001' => 12.75]
        );
    }

    /**
     * Dynamic Bundle Product with tier price + options with catalog rule
     * @magentoDataFixture Magento/Bundle/_files/dynamic_bundle_product_with_tier_price.php
     * @magentoDataFixture Magento/CatalogRule/_files/catalog_rule_for_category_999.php
     *
     * @return void
     */
    public function testDynamicBundleProductWithTierPriceAndOptionsWithCatalogPriceRule(): void
    {
        $this->checkBundlePrices(
            'dynamic_bundle_product_with_tier_price',
            ['price' => 0, 'final_price' => 0, 'min_price' => 7.5, 'max_price' => 15, 'tier_price' => 7.5],
            ['simple1000' => 5.63, 'simple1001' => 11.25]
        );
    }

    /**
     * Dynamic Bundle Product with special price + options without discounts
     * @magentoDataFixture Magento/Bundle/_files/dynamic_bundle_product_with_special_price.php
     *
     * @return void
     */
    public function testDynamicBundleProductWithSpecialPriceAndOptionsWithoutDiscounts(): void
    {
        $this->checkBundlePrices(
            'dynamic_bundle_product_with_special_price',
            ['price' => 0, 'final_price' => 0, 'min_price' => 7.5, 'max_price' => 15, 'tier_price' => null],
            ['simple1000' => 7.5, 'simple1001' => 15]
        );
    }

    /**
     * Dynamic Bundle Product with special price + options with special prices
     * @magentoDataFixture Magento/Bundle/_files/dynamic_bundle_product_with_special_price.php
     *
     * @return void
     */
    public function testDynamicBundleProductWithSpecialPriceAndOptionsWithSpecialPrices(): void
    {
        $this->updateProducts($this->specialPricesForOptionsData());
        $this->checkBundlePrices(
            'dynamic_bundle_product_with_special_price',
            ['price' => 0, 'final_price' => 0, 'min_price' => 6, 'max_price' => 11.25, 'tier_price' => null],
            ['simple1000' => 6, 'simple1001' => 11.25]
        );
    }

    /**
     * Dynamic Bundle Product with special price + options with tier prices
     * @magentoDataFixture Magento/Bundle/_files/dynamic_bundle_product_with_special_price.php
     *
     * @return void
     */
    public function testDynamicBundleProductWithSpecialPriceAndOptionsWithTierPrices(): void
    {
        $this->updateProducts($this->tierPricesForOptionsData());
        $this->checkBundlePrices(
            'dynamic_bundle_product_with_special_price',
            ['price' => 0, 'final_price' => 0, 'min_price' => 6, 'max_price' => 12.75, 'tier_price' => null],
            ['simple1000' => 6, 'simple1001' => 12.75]
        );
    }

    /**
     * Dynamic Bundle Product with special price + options with catalog price rule
     * @magentoDataFixture Magento/Bundle/_files/dynamic_bundle_product_with_special_price.php
     * @magentoDataFixture Magento/CatalogRule/_files/catalog_rule_for_category_999.php
     *
     * @return void
     */
    public function testDynamicBundleProductWithSpecialPriceAndOptionsWithCatalogPriceRule(): void
    {
        $this->checkBundlePrices(
            'dynamic_bundle_product_with_special_price',
            ['price' => 0, 'final_price' => 0, 'min_price' => 5.625, 'max_price' => 11.25, 'tier_price' => null],
            ['simple1000' => 5.63, 'simple1001' => 11.25]
        );
    }

    /**
     * Dynamic Bundle Product with catalog price rule + options without discounts
     * @magentoDataFixture Magento/Bundle/_files/dynamic_bundle_product_with_catalog_rule.php
     *
     * @return void
     */
    public function testDynamicBundleProductWithCatalogPriceRuleAndOptionsWithoutDiscounts(): void
    {
        $this->checkBundlePrices(
            'dynamic_bundle_product_with_catalog_rule',
            ['price' => 0, 'final_price' => 0, 'min_price' => 10, 'max_price' => 20, 'tier_price' => null],
            ['simple1000' => 10, 'simple1001' => 20]
        );
    }

    /**
     * Dynamic Bundle Product with catalog price rule + options with catalog price rule
     * @magentoDataFixture Magento/Bundle/_files/dynamic_bundle_product_with_catalog_rule.php
     * @magentoDataFixture Magento/CatalogRule/_files/catalog_rule_for_category_999.php
     *
     * @return void
     */
    public function testDynamicBundleProductWithCatalogPriceRuleAndOptionsWithCatalogPriceRule(): void
    {
        $this->checkBundlePrices(
            'dynamic_bundle_product_with_catalog_rule',
            ['price' => 0, 'final_price' => 0, 'min_price' => 7.5, 'max_price' => 15, 'tier_price' => null],
            ['simple1000' => 7.5, 'simple1001' => 15]
        );
    }

    /**
     * Dynamic Bundle Product with catalog price rule + options with special prices
     * @magentoDataFixture Magento/Bundle/_files/dynamic_bundle_product_with_catalog_rule.php
     *
     * @return void
     */
    public function testDynamicBundleProductWithCatalogPriceRuleAndOptionsWithSpecialPrices(): void
    {
        $this->updateProducts($this->specialPricesForOptionsData());
        $this->checkBundlePrices(
            'dynamic_bundle_product_with_catalog_rule',
            ['price' => 0, 'final_price' => 0, 'min_price' => 8, 'max_price' => 15, 'tier_price' => null],
            ['simple1000' => 8, 'simple1001' => 15]
        );
    }

    /**
     * Dynamic Bundle Product with catalog price rule + options with tier price
     * @magentoDataFixture Magento/Bundle/_files/dynamic_bundle_product_with_catalog_rule.php
     *
     * @return void
     */
    public function testDynamicBundleProductWithCatalogPriceRuleAndOptionsWithTierPrice(): void
    {
        $this->updateProducts($this->tierPricesForOptionsData());
        $this->checkBundlePrices(
            'dynamic_bundle_product_with_catalog_rule',
            ['price' => 0, 'final_price' => 0, 'min_price' => 8, 'max_price' => 17, 'tier_price' => null],
            ['simple1000' => 8, 'simple1001' => 17]
        );
    }

    /**
     * Check bundle prices from index table and final bundle option price.
     *
     * @param string $sku
     * @param array $indexPrices
     * @param array $expectedPrices
     * @return void
     */
    private function checkBundlePrices(string $sku, array $indexPrices, array $expectedPrices): void
    {
        $product = $this->productRepository->get($sku);
        $this->assertIndexTableData((int)$product->getId(), $indexPrices);
        $this->assertPriceWithChosenOption($product, $expectedPrices);
    }

    /**
     * Asserts price data in index table.
     *
     * @param int $productId
     * @param array $expectedPrices
     * @return void
     */
    private function assertIndexTableData(int $productId, array $expectedPrices): void
    {
        $data = $this->getPriceIndexDataByProductId->execute(
            $productId,
            Group::NOT_LOGGED_IN_ID,
            (int)$this->websiteRepository->get('base')->getId()
        );
        $data = reset($data);
        foreach ($expectedPrices as $column => $price) {
            $this->assertEquals($price, $data[$column]);
        }
    }

    /**
     * Assert bundle final price with chosen option.
     *
     * @param ProductInterface $bundle
     * @param array $expectedPrices
     * @return void
     */
    private function assertPriceWithChosenOption(ProductInterface $bundle, array $expectedPrices): void
    {
        $option = $bundle->getExtensionAttributes()->getBundleProductOptions()[0] ?? null;
        $this->assertNotNull($option);
        foreach ($option->getProductLinks() as $productLink) {
            $bundle->addCustomOption('bundle_selection_ids', $this->json->serialize([$productLink->getId()]));
            $bundle->addCustomOption('selection_qty_' . $productLink->getId(), 1);
            $this->assertEquals(
                round($expectedPrices[$productLink->getSku()], 2),
                round($this->priceModel->getFinalPrice(1, $bundle), 2)
            );
        }
    }

    /**
     * Update products.
     *
     * @param array $products
     * @return void
     */
    private function updateProducts(array $products): void
    {
        foreach ($products as $sku => $updateData) {
            $product = $this->productRepository->get($sku);
            $product->addData($updateData);
            $this->productRepository->save($product);
        }
    }

    /**
     * @return array
     */
    private function specialPricesForOptionsData(): array
    {
        return [
            'simple1000' => [
                'special_price' => 8,
            ],
            'simple1001' => [
                'special_price' => 15,
            ],
        ];
    }

    /**
     * @return array
     */
    private function tierPricesForOptionsData(): array
    {
        return [
            'simple1000' => [
                'tier_price' => [
                    [
                        'website_id' => 0,
                        'cust_group' => Group::CUST_GROUP_ALL,
                        'price_qty' => 1,
                        'value_type' => TierPriceInterface::PRICE_TYPE_FIXED,
                        'price' => 8,
                    ],
                ],
            ],
            'simple1001' => [
                'tier_price' => [
                    [
                        'website_id' => 0,
                        'cust_group' => Group::CUST_GROUP_ALL,
                        'price_qty' => 1,
                        'value_type' => TierPriceInterface::PRICE_TYPE_DISCOUNT,
                        'website_price' => 20,
                        'percentage_value' => 15,
                    ],
                ],
            ],
        ];
    }
}
