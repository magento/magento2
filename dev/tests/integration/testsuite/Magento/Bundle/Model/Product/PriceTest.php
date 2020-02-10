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
use Magento\CatalogRule\Api\CatalogRuleRepositoryInterface;
use Magento\CatalogRule\Api\Data\RuleInterface;
use Magento\CatalogRule\Api\Data\RuleInterfaceFactory;
use Magento\CatalogRule\Model\ResourceModel\Rule\CollectionFactory;
use Magento\CatalogRule\Model\Rule\Condition\Combine;
use Magento\CatalogRule\Model\Rule\Condition\Product;
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
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PriceTest extends TestCase
{
    /** @var int */
    private $defaultWebsiteId;

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

    /** @var RuleInterfaceFactory */
    private $catalogRuleFactory;

    /** @var CatalogRuleRepositoryInterface */
    private $catalogRuleRepository;

    /** @var CollectionFactory */
    private $ruleCollectionFactory;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->priceModel = $this->objectManager->get(Price::class);
        $this->websiteRepository = $this->objectManager->get(WebsiteRepositoryInterface::class);
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $this->productRepository->cleanCache();
        $this->getPriceIndexDataByProductId = $this->objectManager->get(GetPriceIndexDataByProductId::class);
        $this->json = $this->objectManager->get(SerializerInterface::class);
        $this->getCategoryByName = $this->objectManager->get(GetCategoryByName::class);
        $this->catalogRuleFactory = $this->objectManager->get(RuleInterfaceFactory::class);
        $this->catalogRuleRepository = $this->objectManager->get(CatalogRuleRepositoryInterface::class);
        $this->ruleCollectionFactory = $this->objectManager->get(CollectionFactory::class);
        $this->defaultWebsiteId = (int)$this->websiteRepository->get('base')->getId();
    }

    /**
     * @inheritdoc
     */
    protected function tearDown()
    {
        $ruleCollection = $this->ruleCollectionFactory->create();
        $ruleCollection->addFieldToFilter('name', ['eq' => 'Test category rule']);
        $ruleCollection->setPageSize(1);
        $catalogRule = $ruleCollection->getFirstItem();
        if ($catalogRule->getId()) {
            $this->catalogRuleRepository->delete($catalogRule);
        }

        parent::tearDown();
    }

    /**
     * @magentoDataFixture Magento/Bundle/_files/product_with_tier_pricing.php
     *
     * @return void
     */
    public function testGetTierPrice(): void
    {
        $product = $this->productRepository->get('bundle-product');
        // fixture

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
     * @magentoAppArea frontend
     *
     * @return void
     */
    public function testFixedBundleProductPriceWithCatalogRule(): void
    {
        $this->checkBundlePrices(
            'fixed_bundle_product_without_discounts',
            [
                'price' => 50,
                'final_price' => 45,
                'min_price' => 45,
                'max_price' => 75,
                'tier_price' => null,
            ],
            [55, 56.25, 70]
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
            [
                'price' => 50,
                'final_price' => 50,
                'min_price' => 60,
                'max_price' => 75,
                'tier_price' => null,
            ],
            [60, 62.5, 75]
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
            [
                'price' => 50,
                'final_price' => 40,
                'min_price' => 48,
                'max_price' => 60,
                'tier_price' => null,
            ],
            [48, 50, 60]
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
            [
                'price' => 50,
                'final_price' => 50,
                'min_price' => 60,
                'max_price' => 75,
                'tier_price' => 60,
            ],
            [45, 46.88, 56.25]
        );
    }

    /**
     * @magentoDataFixture Magento/Bundle/_files/dynamic_bundle_product_with_tier_price.php
     * @dataProvider dataProviderForBundleProductWithTierPrice
     * @magentoAppArea frontend
     *
     * @param string $updateAction
     * @param array $expectedData
     * @return void
     */
    public function testDynamicBundleProductWithTierPrice(string $updateAction, array $expectedData): void
    {
        $this->prepareDataToTest($updateAction);
        $this->checkBundlePrices(
            'dynamic_bundle_product_with_tier_price',
            $expectedData['bundle_index_prices'],
            $expectedData['prices_with_chosen_option']
        );
    }

    /**
     * @return array
     */
    public function dataProviderForBundleProductWithTierPrice(): array
    {
        return [
            'Dynamic Bundle Product with tier price + options with special prices' => [
                'action' => 'add_special_prices',
                'expected_data' => [
                    'bundle_index_prices' => [
                        'price' => 0,
                        'final_price' => 0,
                        'min_price' => 8,
                        'max_price' => 15,
                        'tier_price' => 8,
                    ],
                    'prices_with_chosen_option' => [6, 11.25]
                ],
            ],
            'Dynamic Bundle Product with tier price + options with tier prices' => [
                'action' => 'add_tier_prices',
                'expected_data' => [
                    'bundle_index_prices' => [
                        'price' => 0,
                        'final_price' => 0,
                        'min_price' => 8,
                        'max_price' => 17,
                        'tier_price' => 8,
                    ],
                    'prices_with_chosen_option' => [6, 12.75]
                ],
            ],
            'Dynamic Bundle Product with tier price + options without discounts' => [
                'action' => '',
                'expected_data' => [
                    'bundle_index_prices' => [
                        'price' => 0,
                        'final_price' => 0,
                        'min_price' => 10,
                        'max_price' => 20,
                        'tier_price' => 10,
                    ],
                    'prices_with_chosen_option' => [7.5, 15]
                ],
            ],
            'Dynamic Bundle Product with tier price + options with catalog rule' => [
                'action' => 'create_catalog_rule',
                'expected_data' => [
                    'bundle_index_prices' => [
                        'price' => 0,
                        'final_price' => 0,
                        'min_price' => 7.5,
                        'max_price' => 15,
                        'tier_price' => 7.5,
                    ],
                    'prices_with_chosen_option' => [5.63, 11.25]
                ],
            ],
        ];
    }

    /**
     * @magentoDataFixture Magento/Bundle/_files/dynamic_bundle_product_with_special_price.php
     * @dataProvider dataProviderForBundleProductWithSpecialPrice
     * @magentoAppArea frontend
     *
     * @param string $updateAction
     * @param array $expectedData
     * @return void
     */
    public function testDynamicBundleProductWithSpecialPrice(string $updateAction, array $expectedData): void
    {
        $this->prepareDataToTest($updateAction);
        $this->checkBundlePrices(
            'dynamic_bundle_product_with_special_price',
            $expectedData['bundle_index_prices'],
            $expectedData['prices_with_chosen_option']
        );
    }

    /**
     * @return array
     */
    public function dataProviderForBundleProductWithSpecialPrice(): array
    {
        return [
            'Dynamic Bundle Product with special price + options with special prices' => [
                'action' => 'add_special_prices',
                'expected_data' => [
                    'bundle_index_prices' => [
                        'price' => 0,
                        'final_price' => 0,
                        'min_price' => 6,
                        'max_price' => 11.25,
                        'tier_price' => null,
                    ],
                    'prices_with_chosen_option' => [6, 11.25]
                ],
            ],
            'Dynamic Bundle Product with special price + options with tier prices' => [
                'action' => 'add_tier_prices',
                'expected_data' => [
                    'bundle_index_prices' => [
                        'price' => 0,
                        'final_price' => 0,
                        'min_price' => 6,
                        'max_price' => 12.75,
                        'tier_price' => null,
                    ],
                    'prices_with_chosen_option' => [6, 12.75]
                ],
            ],
            'Dynamic Bundle Product with special price + options without discounts' => [
                'action' => '',
                'expected_data' => [
                    'bundle_index_prices' => [
                        'price' => 0,
                        'final_price' => 0,
                        'min_price' => 7.5,
                        'max_price' => 15,
                        'tier_price' => null,
                    ],
                    'prices_with_chosen_option' => [7.5, 15]
                ],
            ],
            'Dynamic Bundle Product with special price + options with catalog rule' => [
                'action' => 'create_catalog_rule',
                'expected_data' => [
                    'bundle_index_prices' => [
                        'price' => 0,
                        'final_price' => 0,
                        'min_price' => 5.625,
                        'max_price' => 11.25,
                        'tier_price' => null,
                    ],
                    'prices_with_chosen_option' => [5.63, 11.25]
                ],
            ],
        ];
    }

    /**
     * @magentoDataFixture Magento/Bundle/_files/dynamic_bundle_product_with_catalog_rule.php
     * @magentoAppArea frontend
     * @dataProvider dataProviderForBundleProduct
     *
     * @param string $updateAction
     * @param array $expectedData
     * @return void
     */
    public function testDynamicBundleProductWithCatalogPriceRule(string $updateAction, array $expectedData): void
    {
        $this->prepareDataToTest($updateAction);
        $this->checkBundlePrices(
            'dynamic_bundle_product_with_catalog_rule',
            $expectedData['bundle_index_prices'],
            $expectedData['prices_with_chosen_option']
        );
    }

    /**
     * @magentoDataFixture Magento/Bundle/_files/dynamic_bundle_product_without_discounts.php
     * @dataProvider dataProviderForBundleProduct
     * @magentoAppArea frontend
     *
     * @param string $updateAction
     * @param array $expectedData
     * @return void
     */
    public function testDynamicBundleProductWithoutDiscounts(string $updateAction, array $expectedData): void
    {
        $this->prepareDataToTest($updateAction);
        $this->checkBundlePrices(
            'dynamic_bundle_product_without_discounts',
            $expectedData['bundle_index_prices'],
            $expectedData['prices_with_chosen_option']
        );
    }

    /**
     * @return array
     */
    public function dataProviderForBundleProduct(): array
    {
        return [
            'Dynamic Bundle Product with catalog price rule + options with special prices' => [
                'action' => 'add_special_prices',
                'expected_data' => [
                    'bundle_index_prices' => [
                        'price' => 0,
                        'final_price' => 0,
                        'min_price' => 8,
                        'max_price' => 15,
                        'tier_price' => null,
                    ],
                    'prices_with_chosen_option' => [8, 15]
                ]
            ],
            'Dynamic Bundle Product with catalog price rule + options with tier prices' => [
                'action' => 'add_tier_prices',
                'expected_data' => [
                    'bundle_index_prices' => [
                        'price' => 0,
                        'final_price' => 0,
                        'min_price' => 8,
                        'max_price' => 17,
                        'tier_price' => null,
                    ],
                    'prices_with_chosen_option' => [8, 17]
                ]
            ],
            'Dynamic Bundle Product with catalog price rule + options without discounts' => [
                'action' => '',
                'expected_data' => [
                    'bundle_index_prices' => [
                        'price' => 0,
                        'final_price' => 0,
                        'min_price' => 10,
                        'max_price' => 20,
                        'tier_price' => null,
                    ],
                    'prices_with_chosen_option' => [10, 20]
                ]
            ],
            'Dynamic Bundle Product with catalog price rule + options with catalog rule' => [
                'action' => 'create_catalog_rule',
                'expected_data' => [
                    'bundle_index_prices' => [
                        'price' => 0,
                        'final_price' => 0,
                        'min_price' => 7.5,
                        'max_price' => 15,
                        'tier_price' => null,
                    ],
                    'prices_with_chosen_option' => [7.5, 15]
                ],
            ],
        ];
    }

    /**
     * Prepare data to test.
     *
     * @param string $updateAction
     * @return void
     */
    private function prepareDataToTest(string $updateAction): void
    {
        switch ($updateAction) {
            case 'add_special_prices':
                $this->updateProducts($this->specialPricesForOptionsData());
                break;
            case 'add_tier_prices':
                $this->updateProducts($this->tierPricesForOptionsData());
                break;
            case 'create_catalog_rule':
                $this->createCatalogRule();
                break;
            default:
                break;
        }
    }

    /**
     * Check bundle prices from index table and final bundle option price.
     *
     * @param string $sku
     * @param array $indexPrices
     * @param array $expectedPrices
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
            $this->defaultWebsiteId
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
     */
    private function assertPriceWithChosenOption(ProductInterface $bundle, array $expectedPrices): void
    {
        $option = $bundle->getExtensionAttributes()->getBundleProductOptions()[0];
        foreach ($option->getProductLinks() as $id => $productLink) {
            $bundle->addCustomOption('bundle_selection_ids', $this->json->serialize([$productLink->getId()]));
            $bundle->addCustomOption('selection_qty_' . $productLink->getId(), 1);
            $this->assertEquals(
                round($expectedPrices[$id], 2),
                round($this->priceModel->getFinalPrice(1, $bundle), 2)
            );
        }
    }

    /**
     * Update products.
     *
     * @param array $data
     * @return void
     */
    private function updateProducts(array $data): void
    {
        foreach ($data as $sku => $updateData) {
            $product = $this->productRepository->get($sku);
            $product->addData($updateData);
            $this->productRepository->save($product);
        }
    }

    /**
     * Create catalog rule.
     *
     * @return void
     */
    private function createCatalogRule(): void
    {
        $category = $this->getCategoryByName->execute('Category 999');
        $ruleData = [
            RuleInterface::NAME => 'Test category rule',
            RuleInterface::IS_ACTIVE => 1,
            'website_ids' => [$this->defaultWebsiteId],
            'customer_group_ids' => Group::NOT_LOGGED_IN_ID,
            RuleInterface::DISCOUNT_AMOUNT => 25,
            RuleInterface::SIMPLE_ACTION => 'by_percent',
            'conditions' => [
                '1' => [
                    'type' => Combine::class,
                    'aggregator' => 'all',
                    'value' => '1',
                ],
                '1--1' => [
                    'type' => Product::class,
                    'attribute' => 'category_ids',
                    'operator' => '==',
                    'value' => $category->getId(),
                ],
            ],
        ];
        $catalogRule = $this->catalogRuleFactory->create();
        $catalogRule->loadPost($ruleData);
        $this->catalogRuleRepository->save($catalogRule);
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
