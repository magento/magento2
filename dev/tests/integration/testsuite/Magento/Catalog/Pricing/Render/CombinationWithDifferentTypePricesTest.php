<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Pricing\Render;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductTierPriceExtensionFactory;
use Magento\Catalog\Api\Data\ProductTierPriceInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogRule\Api\CatalogRuleRepositoryInterface;
use Magento\CatalogRule\Api\Data\RuleInterface;
use Magento\CatalogRule\Api\Data\RuleInterfaceFactory;
use Magento\CatalogRule\Model\Indexer\IndexBuilder;
use Magento\CatalogRule\Model\ResourceModel\Rule\Product\Price;
use Magento\Customer\Model\Group;
use Magento\Customer\Model\Session;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\Page;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\Xpath;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Assertions related to check product price rendering with combination of different price types.
 *
 * @magentoDbIsolation disabled
 * @magentoAppArea frontend
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CombinationWithDifferentTypePricesTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var Page
     */
    private $page;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var IndexBuilder
     */
    private $indexBuilder;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var RuleInterface[]
     */
    private $createdRules = [];

    /**
     * @var WebsiteRepositoryInterface
     */
    private $websiteRepository;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var RuleInterfaceFactory
     */
    private $catalogRuleFactory;

    /**
     * @var CatalogRuleRepositoryInterface
     */
    private $catalogRuleRepository;

    /**
     * @var ProductTierPriceInterfaceFactory
     */
    private $productTierPriceFactory;

    /**
     * @var Price
     */
    private $catalogRuleProductPriceResource;

    /**
     * @var ProductTierPriceExtensionFactory
     */
    private $productTierPriceExtensionFactory;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();
        $this->objectManager = Bootstrap::getObjectManager();
        $this->page = $this->objectManager->create(Page::class);
        $this->registry = $this->objectManager->get(Registry::class);
        $this->indexBuilder = $this->objectManager->get(IndexBuilder::class);
        $this->customerSession = $this->objectManager->get(Session::class);
        $this->websiteRepository = $this->objectManager->get(WebsiteRepositoryInterface::class);
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $this->catalogRuleFactory = $this->objectManager->get(RuleInterfaceFactory::class);
        $this->catalogRuleRepository = $this->objectManager->get(CatalogRuleRepositoryInterface::class);
        $this->productTierPriceFactory = $this->objectManager->get(ProductTierPriceInterfaceFactory::class);
        $this->catalogRuleProductPriceResource = $this->objectManager->get(Price::class);
        $this->productTierPriceExtensionFactory = $this->objectManager->get(ProductTierPriceExtensionFactory::class);
        $this->productRepository->cleanCache();
    }

    /**
     * @inheritdoc
     */
    protected function tearDown()
    {
        parent::tearDown();
        $this->registry->unregister('product');
        $this->cleanCatalogRuleData();
    }

    /**
     * Assert that product price rendered with expected special and regular prices if
     * product has special price which lower than regular and tier prices.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_special_price.php
     *
     * @dataProvider tierPricesForAllCustomerGroupsDataProvider
     *
     * @param float $specialPrice
     * @param float $regularPrice
     * @param array $tierPrices
     * @param array|null $tierMessageConfig
     * @return void
     */
    public function testRenderSpecialPriceWithCombinationWithTierPrice(
        float $specialPrice,
        float $regularPrice,
        array $tierPrices,
        ?array $tierMessageConfig
    ): void {
        $this->processCheckRenderedPrices($specialPrice, $regularPrice, $tierPrices, $tierMessageConfig);
    }

    /**
     * Data provider with tier prices which are for all customers groups.
     *
     * @return array
     */
    public function tierPricesForAllCustomerGroupsDataProvider(): array
    {
        return [
            'fixed_tier_price_with_qty_1' => [
                5.99,
                10,
                [
                    [
                        'customer_group_id' => Group::CUST_GROUP_ALL,
                        'qty' => 1,
                        'value' => 9
                    ],
                ],
                null
            ],
            'fixed_tier_price_with_qty_2' => [
                5.99,
                10,
                [
                    [
                        'customer_group_id' => Group::CUST_GROUP_ALL,
                        'qty' => 2,
                        'value' => 5
                    ],
                ],
                [
                    'qty' => 2,
                    'price' => 5.00,
                    'percent' => 17,
                ],
            ],
            'percent_tier_price_with_qty_2' => [
                5.99,
                10,
                [
                    [
                        'customer_group_id' => Group::CUST_GROUP_ALL,
                        'qty' => 2,
                        'percent_value' => 70
                    ],
                ],
                [
                    'qty' => 2,
                    'price' => 3.00,
                    'percent' => 70,
                ],
            ],
            'fixed_tier_price_with_qty_1_is_lower_than_special' => [
                5,
                10,
                [
                    [
                        'customer_group_id' => Group::CUST_GROUP_ALL,
                        'qty' => 1,
                        'value' => 5
                    ],
                ],
                null
            ],
            'percent_tier_price_with_qty_1_is_lower_than_special' => [
                3,
                10,
                [
                    [
                        'customer_group_id' => Group::NOT_LOGGED_IN_ID,
                        'qty' => 1,
                        'percent_value' => 70
                    ],
                ],
                null
            ],
        ];
    }

    /**
     * Assert that product price rendered with expected special and regular prices if
     * product has special price which lower than regular and tier prices and customer is logged.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_special_price.php
     * @magentoDataFixture Magento/Customer/_files/customer.php
     *
     * @magentoAppIsolation enabled
     *
     * @dataProvider tierPricesForLoggedCustomerGroupDataProvider
     *
     * @param float $specialPrice
     * @param float $regularPrice
     * @param array $tierPrices
     * @param array|null $tierMessageConfig
     * @return void
     */
    public function testRenderSpecialPriceWithCombinationWithTierPriceWithLoggedUser(
        float $specialPrice,
        float $regularPrice,
        array $tierPrices,
        ?array $tierMessageConfig
    ): void {
        try {
            $this->customerSession->setCustomerId(1);
            $this->processCheckRenderedPrices($specialPrice, $regularPrice, $tierPrices, $tierMessageConfig);
        } finally {
            $this->customerSession->setCustomerId(null);
        }
    }

    /**
     * Data provider with tier prices which are for logged customers group.
     *
     * @return array
     */
    public function tierPricesForLoggedCustomerGroupDataProvider(): array
    {
        return [
            'fixed_tier_price_with_qty_1' => [
                5.99,
                10,
                [
                    [
                        'customer_group_id' => 1,
                        'qty' => 1,
                        'value' => 9
                    ],
                ],
                null
            ],
            'percent_tier_price_with_qty_1' => [
                5.99,
                10,
                [
                    [
                        'customer_group_id' => 1,
                        'qty' => 1,
                        'percent_value' => 30
                    ],
                ],
                null
            ],
        ];
    }

    /**
     * Assert that product price rendered with expected special and regular prices if
     * product has catalog rule price with different type of prices.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_special_price.php
     *
     * @dataProvider catalogRulesDataProvider
     *
     * @param float $specialPrice
     * @param float $regularPrice
     * @param array $catalogRules
     * @param array $tierPrices
     * @param array|null $tierMessageConfig
     * @return void
     */
    public function testRenderCatalogRulePriceWithCombinationWithDifferentTypePrices(
        float $specialPrice,
        float $regularPrice,
        array $catalogRules,
        array $tierPrices,
        ?array $tierMessageConfig
    ): void {
        $this->createCatalogRulesForProduct($catalogRules);
        $this->indexBuilder->reindexFull();
        $this->processCheckRenderedPrices($specialPrice, $regularPrice, $tierPrices, $tierMessageConfig);
    }

    /**
     * Data provider with expect special and regular price, catalog rule data and tier price.
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     *
     * @return array
     */
    public function catalogRulesDataProvider(): array
    {
        return [
            'fixed_catalog_rule_price_more_than_special_price' => [
                5.99,
                10,
                [
                    [
                        RuleInterface::DISCOUNT_AMOUNT => 2,
                    ],
                ],
                [],
                null
            ],
            'fixed_catalog_rule_price_lower_than_special_price' => [
                2,
                10,
                [
                    [
                        RuleInterface::DISCOUNT_AMOUNT => 8,
                    ],
                ],
                [],
                null
            ],
            'fixed_catalog_rule_price_more_than_tier_price' => [
                4,
                10,
                [
                    [
                        RuleInterface::DISCOUNT_AMOUNT => 6,
                    ],
                ],
                [
                    [
                        'customer_group_id' => Group::CUST_GROUP_ALL,
                        'qty' => 2,
                        'percent_value' => 70
                    ],
                ],
                [
                    'qty' => 2,
                    'price' => 3.00,
                    'percent' => 70,
                ],
            ],
            'fixed_catalog_rule_price_lower_than_tier_price' => [
                2,
                10,
                [
                    [
                        RuleInterface::DISCOUNT_AMOUNT => 7,
                    ],
                ],
                [
                    [
                        'customer_group_id' => Group::CUST_GROUP_ALL,
                        'qty' => 1,
                        'value' => 2
                    ],
                ],
                null
            ],
            'adjust_percent_catalog_rule_price_lower_than_special_price' => [
                4.50,
                10,
                [
                    [
                        RuleInterface::DISCOUNT_AMOUNT => 45,
                        RuleInterface::SIMPLE_ACTION => 'to_percent',
                    ],
                ],
                [],
                null
            ],
            'adjust_percent_catalog_rule_price_lower_than_tier_price' => [
                3,
                10,
                [
                    [
                        RuleInterface::DISCOUNT_AMOUNT => 30,
                        RuleInterface::SIMPLE_ACTION => 'to_percent',
                    ],
                ],
                [
                    [
                        'customer_group_id' => Group::CUST_GROUP_ALL,
                        'qty' => 1,
                        'value' => 3.50
                    ],
                ],
                null
            ],
            'percent_catalog_rule_price_lower_than_special_price' => [
                2,
                10,
                [
                    [
                        RuleInterface::DISCOUNT_AMOUNT => 2,
                        RuleInterface::SIMPLE_ACTION => 'to_fixed',
                    ],
                ],
                [],
                null
            ],
            'percent_catalog_rule_price_lower_than_tier_price' => [
                1,
                10,
                [
                    [
                        RuleInterface::DISCOUNT_AMOUNT => 1,
                        RuleInterface::SIMPLE_ACTION => 'to_fixed',
                    ],
                ],
                [
                    [
                        'customer_group_id' => Group::CUST_GROUP_ALL,
                        'qty' => 1,
                        'value' => 3
                    ],
                ],
                null
            ],
        ];
    }

    /**
     * Check that price html contain all provided prices.
     *
     * @param string $priceHtml
     * @param float $specialPrice
     * @param float $regularPrice
     */
    private function checkPrices(string $priceHtml, float $specialPrice, float $regularPrice): void
    {
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath($this->getSpecialPriceXpath($specialPrice), $priceHtml),
            "Special price {$specialPrice} is not as expected. Rendered html: {$priceHtml}"
        );
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath($this->getRegularPriceLabelXpath(), $priceHtml),
            "Regular price label 'Regular Price' not founded. Rendered html: {$priceHtml}"
        );
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath($this->getRegularPriceXpath($regularPrice), $priceHtml),
            "Regular price {$regularPrice} is not as expected. Rendered html: {$priceHtml}"
        );
    }

    /**
     * Assert that  tier price message.
     *
     * @param string $priceHtml
     * @param array $tierMessageConfig
     */
    private function checkTierPriceMessage(string $priceHtml, array $tierMessageConfig): void
    {
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath($this->getTierPriceMessageXpath($tierMessageConfig), $priceHtml),
            "Tier price message not founded. Rendered html: {$priceHtml}"
        );
    }

    /**
     * Render price render template with product.
     *
     * @param ProductInterface $product
     * @return string
     */
    private function getPriceHtml(ProductInterface $product): string
    {
        $this->registerProduct($product);
        $this->page->addHandle([
            'default',
            'catalog_product_view',
        ]);
        $this->page->getLayout()->generateXml();
        $priceHtml = '';
        $availableChildNames = [
            'product.info.price',
            'product.price.tier'
        ];
        foreach ($this->page->getLayout()->getChildNames('product.info.main') as $childName) {
            if (in_array($childName, $availableChildNames, true)) {
                $priceHtml .= $this->page->getLayout()->renderElement($childName, false);
            }
        }

        return $priceHtml;
    }

    /**
     * @param ProductInterface $product
     * @return void
     */
    private function registerProduct(ProductInterface $product): void
    {
        $this->registry->unregister('product');
        $this->registry->register('product', $product);
    }

    /**
     * @param ProductInterface $product
     * @param array $tierPrices
     * @return ProductInterface
     */
    private function createTierPricesForProduct(ProductInterface $product, array $tierPrices): ProductInterface
    {
        if (empty($tierPrices)) {
            return $product;
        }

        $adminWebsite = $this->websiteRepository->get('admin');
        $createdTierPrices = [];
        foreach ($tierPrices as $tierPrice) {
            $tierPriceExtensionAttribute = $this->productTierPriceExtensionFactory->create();
            $tierPriceExtensionAttribute->setWebsiteId($adminWebsite->getId());

            if (isset($tierPrice['percent_value'])) {
                $tierPriceExtensionAttribute->setPercentageValue($tierPrice['percent_value']);
                unset($tierPrice['percent_value']);
            }

            $createdTierPrices[] = $this->productTierPriceFactory->create(
                [
                    'data' => $tierPrice
                ]
            )->setExtensionAttributes($tierPriceExtensionAttribute);
        }
        $product->setTierPrices($createdTierPrices);

        return $this->productRepository->save($product);
    }

    /**
     * @param float $specialPrice
     * @return string
     */
    private function getSpecialPriceXpath(float $specialPrice): string
    {
        $pathsForSearch = [
            "//div[@class='price-box price-final_price']",
            "//span[@class='special-price']",
            sprintf("//span[@class='price' and text()='$%01.2f']", $specialPrice),
        ];

        return implode('', $pathsForSearch);
    }

    /**
     * @param float $regularPrice
     * @return string
     */
    private function getRegularPriceXpath(float $regularPrice): string
    {
        $pathsForSearch = [
            "//div[@class='price-box price-final_price']",
            "//span[@class='old-price']",
            "//span[contains(@class, 'price-container')]",
            sprintf("//span[@class='price' and text()='$%01.2f']", $regularPrice),
        ];

        return implode('', $pathsForSearch);
    }

    /**
     * @return string
     */
    private function getRegularPriceLabelXpath(): string
    {
        $pathsForSearch = [
            "//div[@class='price-box price-final_price']",
            "//span[@class='old-price']",
            "//span[contains(@class, 'price-container')]",
            "//span[text()='Regular Price']",
        ];

        return implode('', $pathsForSearch);
    }

    /**
     * @param array $expectedMessage
     * @return string
     */
    private function getTierPriceMessageXpath(array $expectedMessage): string
    {
        [$qty, $price, $percent] = array_values($expectedMessage);
        $liPaths = [
            "@class='item' and contains(text(), 'Buy {$qty} for')",
            sprintf("//span[@class='price' and text()='$%01.2f']", $price),
            "//span[contains(@class, 'percent') and contains(text(), '{$percent}')]",
        ];

        return sprintf("//ul[@class='prices-tier items']//li[%s]", implode(' and ', $liPaths));
    }

    /**
     * Process test with combination of special and tier price.
     *
     * @param float $specialPrice
     * @param float $regularPrice
     * @param array $tierPrices
     * @param array|null $tierMessageConfig
     */
    private function processCheckRenderedPrices(
        float $specialPrice,
        float $regularPrice,
        array $tierPrices,
        ?array $tierMessageConfig
    ): void {
        $product = $this->productRepository->get('simple', false, null, true);
        $product = $this->createTierPricesForProduct($product, $tierPrices);
        $priceHtml = $this->getPriceHtml($product);
        $this->checkPrices($priceHtml, $specialPrice, $regularPrice);
        if (null !== $tierMessageConfig) {
            $this->checkTierPriceMessage($priceHtml, $tierMessageConfig);
        }
    }

    /**
     * @param array $catalogRules
     * @return void
     */
    private function createCatalogRulesForProduct(array $catalogRules): void
    {
        $baseWebsite = $this->websiteRepository->get('base');
        $staticRuleData = [
            RuleInterface::IS_ACTIVE => 1,
            RuleInterface::NAME => 'Test rule name.',
            'customer_group_ids' => Group::NOT_LOGGED_IN_ID,
            RuleInterface::SIMPLE_ACTION => 'by_fixed',
            RuleInterface::STOP_RULES_PROCESSING => false,
            RuleInterface::SORT_ORDER => 0,
            'sub_is_enable' => 0,
            'sub_discount_amount' => 0,
            'website_ids' => [$baseWebsite->getId()]
        ];

        foreach ($catalogRules as $catalogRule) {
            $catalogRule = array_replace($staticRuleData, $catalogRule);
            $catalogRule = $this->catalogRuleFactory->create(['data' => $catalogRule]);
            $this->createdRules[] = $this->catalogRuleRepository->save($catalogRule);
        }
    }

    /**
     * Clean data in DB which created during catalog rule create.
     *
     * @return void
     */
    private function cleanCatalogRuleData(): void
    {
        foreach ($this->createdRules as $createdRule) {
            $this->catalogRuleRepository->delete($createdRule);
        }
        $this->catalogRuleProductPriceResource->getConnection()
            ->delete($this->catalogRuleProductPriceResource->getMainTable());
    }
}
