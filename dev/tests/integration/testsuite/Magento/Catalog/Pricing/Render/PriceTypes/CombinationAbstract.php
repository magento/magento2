<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Pricing\Render\PriceTypes;

use Magento\Catalog\Api\Data\ProductCustomOptionInterface;
use Magento\Catalog\Api\Data\ProductCustomOptionInterfaceFactory;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductTierPriceExtensionFactory;
use Magento\Catalog\Api\Data\ProductTierPriceInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Config\Source\ProductPriceOptionsInterface;
use Magento\Catalog\Model\Product\Option;
use Magento\CatalogRule\Api\CatalogRuleRepositoryInterface;
use Magento\CatalogRule\Api\Data\RuleInterface;
use Magento\CatalogRule\Api\Data\RuleInterfaceFactory;
use Magento\CatalogRule\Model\Indexer\IndexBuilder;
use Magento\Customer\Model\Group;
use Magento\Customer\Model\Session;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\Xpath;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Base class for combination of different price types tests.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class CombinationAbstract extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var Page
     */
    protected $page;

    /**
     * @var IndexBuilder
     */
    protected $indexBuilder;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Registry
     */
    private $registry;

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
     * @var ProductTierPriceExtensionFactory
     */
    private $productTierPriceExtensionFactory;

    /**
     * @var ProductCustomOptionInterfaceFactory
     */
    private $productCustomOptionFactory;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->objectManager = Bootstrap::getObjectManager();
        $this->page = $this->objectManager->get(PageFactory::class)->create();
        $this->registry = $this->objectManager->get(Registry::class);
        $this->indexBuilder = $this->objectManager->get(IndexBuilder::class);
        $this->customerSession = $this->objectManager->get(Session::class);
        $this->websiteRepository = $this->objectManager->get(WebsiteRepositoryInterface::class);
        $this->storeManager = $this->objectManager->get(StoreManagerInterface::class);
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $this->productRepository->cleanCache();
        $this->catalogRuleFactory = $this->objectManager->get(RuleInterfaceFactory::class);
        $this->catalogRuleRepository = $this->objectManager->get(CatalogRuleRepositoryInterface::class);
        $this->productTierPriceFactory = $this->objectManager->get(ProductTierPriceInterfaceFactory::class);
        $this->productTierPriceExtensionFactory = $this->objectManager->get(ProductTierPriceExtensionFactory::class);
        $this->productCustomOptionFactory = $this->objectManager->get(ProductCustomOptionInterfaceFactory::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->registry->unregister('product');
        $this->registry->unregister('current_product');
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
                'special_price' => 5.99,
                'regular_price' => 10,
                'tier_data' => [
                    'prices' => [['customer_group_id' => Group::CUST_GROUP_ALL, 'qty' => 1, 'value' => 9]],
                    'message_config' => null,
                ],
            ],
            'fixed_tier_price_with_qty_2' => [
                'special_price' => 5.99,
                'regular_price' => 10,
                'tier_data' => [
                    'prices' => [['customer_group_id' => Group::CUST_GROUP_ALL, 'qty' => 2, 'value' => 5]],
                    'message_config' => ['qty' => 2, 'price' => 5.00, 'percent' => 17],
                ],
            ],
            'percent_tier_price_with_qty_2' => [
                'special_price' => 5.99,
                'regular_price' => 10,
                'tier_data' => [
                    'prices' => [['customer_group_id' => Group::CUST_GROUP_ALL, 'qty' => 2, 'percent_value' => 70]],
                    'message_config' => ['qty' => 2, 'price' => 3.00, 'percent' => 70],
                ],
            ],
            'fixed_tier_price_with_qty_1_is_lower_than_special' => [
                'special_price' => 5,
                'regular_price' => 10,
                'tier_data' => [
                    'prices' => [['customer_group_id' => Group::CUST_GROUP_ALL, 'qty' => 1, 'value' => 5]],
                    'message_config' => null,
                ],
            ],
            'percent_tier_price_with_qty_1_is_lower_than_special' => [
                'special_price' => 3,
                'regular_price' => 10,
                'tier_data' => [
                    'prices' => [['customer_group_id' => Group::NOT_LOGGED_IN_ID, 'qty' => 1, 'percent_value' => 70]],
                    'message_config' => null,
                ],
            ],
        ];
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
                'special_price' => 5.99,
                'regular_price' => 10,
                'tier_data' => [
                    'prices' => [['customer_group_id' => 1, 'qty' => 1, 'value' => 9]],
                    'message_config' => null,
                ],
            ],
            'percent_tier_price_with_qty_1' => [
                'special_price' => 5.99,
                'regular_price' => 10,
                'tier_data' => [
                    'prices' => [['customer_group_id' => 1, 'qty' => 1, 'percent_value' => 30]],
                    'message_config' => null,
                ],
            ],
        ];
    }

    /**
     * Data provider with expect special and regular price, catalog rule data and tier price.
     *
     * @return array
     */
    public function catalogRulesDataProvider(): array
    {
        return [
            'fixed_catalog_rule_price_more_than_special_price' => [
                'special_price' => 5.99,
                'regular_price' => 10,
                'catalog_rules' => [
                    [RuleInterface::DISCOUNT_AMOUNT => 2],
                ],
                'tier_data' => ['prices' => [], 'message_config' => null],
            ],
            'fixed_catalog_rule_price_lower_than_special_price' => [
                'special_price' => 2,
                'regular_price' => 10,
                'catalog_rules' => [
                    [RuleInterface::DISCOUNT_AMOUNT => 8],
                ],
                'tier_data' => ['prices' => [], 'message_config' => null],
            ],
            'fixed_catalog_rule_price_more_than_tier_price' => [
                'special_price' => 4,
                'regular_price' => 10,
                'catalog_rules' => [
                    [RuleInterface::DISCOUNT_AMOUNT => 6],
                ],
                'tier_data' => [
                    'prices' => [['customer_group_id' => Group::CUST_GROUP_ALL, 'qty' => 2, 'percent_value' => 70]],
                    'message_config' => ['qty' => 2, 'price' => 3.00, 'percent' => 70],
                ],
            ],
            'fixed_catalog_rule_price_lower_than_tier_price' => [
                'special_price' => 2,
                'regular_price' => 10,
                'catalog_rules' => [
                    [RuleInterface::DISCOUNT_AMOUNT => 7],
                ],
                'tier_data' => [
                    'prices' => [['customer_group_id' => Group::CUST_GROUP_ALL, 'qty' => 1, 'value' => 2]],
                    'message_config' => null,
                ],
            ],
            'adjust_percent_catalog_rule_price_lower_than_special_price' => [
                'special_price' => 4.50,
                'regular_price' => 10,
                'catalog_rules' => [
                    [RuleInterface::DISCOUNT_AMOUNT => 45, RuleInterface::SIMPLE_ACTION => 'to_percent'],
                ],
                'tier_data' => ['prices' => [], 'message_config' => null],
            ],
            'adjust_percent_catalog_rule_price_lower_than_tier_price' => [
                'special_price' => 3,
                'regular_price' => 10,
                'catalog_rules' => [
                    [RuleInterface::DISCOUNT_AMOUNT => 30, RuleInterface::SIMPLE_ACTION => 'to_percent'],
                ],
                'tier_data' => [
                    'prices' => [['customer_group_id' => Group::CUST_GROUP_ALL, 'qty' => 1, 'value' => 3.50]],
                    'message_config' => null,
                ],
            ],
            'percent_catalog_rule_price_lower_than_special_price' => [
                'special_price' => 2,
                'regular_price' => 10,
                'catalog_rules' => [
                    [RuleInterface::DISCOUNT_AMOUNT => 2, RuleInterface::SIMPLE_ACTION => 'to_fixed'],
                ],
                'tier_data' => ['prices' => [], 'message_config' => null],
            ],
            'percent_catalog_rule_price_lower_than_tier_price' => [
                'special_price' => 1,
                'regular_price' => 10,
                'catalog_rules' => [
                    [RuleInterface::DISCOUNT_AMOUNT => 1, RuleInterface::SIMPLE_ACTION => 'to_fixed'],
                ],
                'tier_data' => [
                    'prices' => [['customer_group_id' => Group::CUST_GROUP_ALL, 'qty' => 1, 'value' => 3]],
                    'message_config' => null,
                ],
            ],
        ];
    }

    /**
     * Data provider with percent customizable option prices.
     *
     * @return array
     */
    public function percentCustomOptionsDataProvider(): array
    {
        return [
            'percent_option_for_product_without_special_price' => [
                'option_price' => 5,
                'product_prices' => ['special_price' => null],
            ],
            'percent_option_for_product_with_special_price' => [
                'option_price' => 3,
                'product_prices' => ['special_price' => 5.99],
            ],
        ];
    }

    /**
     * Check that price html contain all provided prices.
     *
     * @param string $priceHtml
     * @param float $specialPrice
     * @param float $regularPrice
     * @return void
     */
    protected function checkPrices(string $priceHtml, float $specialPrice, float $regularPrice): void
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
     * Assert that tier price message.
     *
     * @param string $priceHtml
     * @param array $tierMessageConfig
     * @return void
     */
    protected function checkTierPriceMessage(string $priceHtml, array $tierMessageConfig): void
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
    protected function getPriceHtml(ProductInterface $product): string
    {
        $this->preparePageLayout($product);
        $priceHtml = $this->page->getLayout()->renderElement('product.info.price', false);
        $priceHtml .= $this->page->getLayout()->renderElement('product.price.tier', false);

        return $priceHtml;
    }

    /**
     * Render custom options price render template with product.
     *
     * @param ProductInterface $product
     * @return string
     */
    protected function getCustomOptionsPriceHtml(ProductInterface $product): string
    {
        $this->preparePageLayout($product);

        return  $this->page->getLayout()->renderElement('product.info.options', false);
    }

    /**
     * Add product to the registry.
     *
     * @param ProductInterface $product
     * @return void
     */
    protected function registerProduct(ProductInterface $product): void
    {
        $this->registry->unregister('product');
        $this->registry->register('product', $product);
        $this->registry->unregister('current_product');
        $this->registry->register('current_product', $product);
    }

    /**
     * Create provided tier prices for product.
     *
     * @param ProductInterface $product
     * @param array $tierPrices
     * @param int $websiteId
     * @return ProductInterface
     */
    protected function createTierPricesForProduct(
        ProductInterface $product,
        array $tierPrices,
        int $websiteId
    ): ProductInterface {
        if (empty($tierPrices)) {
            return $product;
        }

        $createdTierPrices = [];
        foreach ($tierPrices as $tierPrice) {
            $tierPriceExtensionAttribute = $this->productTierPriceExtensionFactory->create();
            $tierPriceExtensionAttribute->setWebsiteId($websiteId);

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
     * Add custom option to product with data.
     *
     * @param ProductInterface $product
     * @return void
     */
    protected function addOptionToProduct(ProductInterface $product): void
    {
        $optionData = [
            Option::KEY_PRODUCT_SKU => $product->getSku(),
            Option::KEY_TITLE => 'Test option field title',
            Option::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_FIELD,
            Option::KEY_IS_REQUIRE => 0,
            Option::KEY_PRICE => 50,
            Option::KEY_PRICE_TYPE => ProductPriceOptionsInterface::VALUE_PERCENT,
            Option::KEY_SKU => 'test-option-field-title',
        ];
        $option = $this->productCustomOptionFactory->create(['data' => $optionData]);
        $option->setProductSku($product->getSku());
        $product->setOptions([$option]);
        $product->setHasOptions(true);
    }

    /**
     * Returns xpath for special price.
     *
     * @param float $specialPrice
     * @return string
     */
    protected function getSpecialPriceXpath(float $specialPrice): string
    {
        $pathsForSearch = [
            "//div[contains(@class, 'price-box') and contains(@class, 'price-final_price')]",
            "//span[contains(@class, 'special-price')]",
            sprintf("//span[contains(@class, 'price') and text()='$%01.2f']", $specialPrice),
        ];

        return implode('', $pathsForSearch);
    }

    /**
     * Returns xpath for regular price.
     *
     * @param float $regularPrice
     * @return string
     */
    protected function getRegularPriceXpath(float $regularPrice): string
    {
        $pathsForSearch = [
            "//div[contains(@class, 'price-box') and contains(@class, 'price-final_price')]",
            "//span[contains(@class, 'old-price')]",
            "//span[contains(@class, 'price-container')]",
            sprintf("//span[contains(@class, 'price') and text()='$%01.2f']", $regularPrice),
        ];

        return implode('', $pathsForSearch);
    }

    /**
     * Returns xpath for regular price label.
     *
     * @return string
     */
    protected function getRegularPriceLabelXpath(): string
    {
        $pathsForSearch = [
            "//div[contains(@class, 'price-box') and contains(@class, 'price-final_price')]",
            "//span[contains(@class, 'old-price')]",
            "//span[contains(@class, 'price-container')]",
            sprintf("//span[normalize-space(text())='%s']", __('Regular Price')),
        ];

        return implode('', $pathsForSearch);
    }

    /**
     * Return tier price message xpath. Message must contain expected quantity, price and discount percent.
     *
     * @param array $expectedMessage
     * @return string
     */
    protected function getTierPriceMessageXpath(array $expectedMessage): string
    {
        [$qty, $price, $percent] = array_values($expectedMessage);
        $liPaths = [
            "contains(@class, 'item') and contains(text(), 'Buy {$qty} for')",
            sprintf("//span[contains(@class, 'price') and text()='$%01.2f']", $price),
            "//span[contains(@class, 'percent') and contains(text(), '{$percent}')]",
        ];

        return sprintf(
            "//ul[contains(@class, 'prices-tier') and contains(@class, 'items')]//li[%s]",
            implode(' and ', $liPaths)
        );
    }

    /**
     * Process test with combination of special and tier price.
     *
     * @param string $sku
     * @param float $specialPrice
     * @param float $regularPrice
     * @param array $tierData
     * @param int $websiteId
     * @return void
     */
    public function assertRenderedPrices(
        string $sku,
        float $specialPrice,
        float $regularPrice,
        array $tierData,
        int $websiteId = 0
    ): void {
        $product = $this->getProduct($sku);
        $product = $this->createTierPricesForProduct($product, $tierData['prices'], $websiteId);
        $priceHtml = $this->getPriceHtml($product);
        $this->checkPrices($priceHtml, $specialPrice, $regularPrice);
        if (null !== $tierData['message_config']) {
            $this->checkTierPriceMessage($priceHtml, $tierData['message_config']);
        }
    }

    /**
     * Process test with combination of special and custom option price.
     *
     * @param string $sku
     * @param float $optionPrice
     * @param array $productPrices
     * @return void
     */
    public function assertRenderedCustomOptionPrices(
        string $sku,
        float $optionPrice,
        array $productPrices
    ): void {
        $product = $this->getProduct($sku);
        $product->addData($productPrices);
        $this->addOptionToProduct($product);
        $this->productRepository->save($product);
        $priceHtml = $this->getCustomOptionsPriceHtml($this->getProduct($sku));
        $this->assertStringContainsString(sprintf('data-price-amount="%s"', $optionPrice), $priceHtml);
    }

    /**
     * Create provided catalog rules.
     *
     * @param array $catalogRules
     * @param string $websiteCode
     * @return void
     */
    protected function createCatalogRulesForProduct(array $catalogRules, string $websiteCode): void
    {
        $baseWebsite = $this->websiteRepository->get($websiteCode);
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
            $this->catalogRuleRepository->save($catalogRule);
        }
    }

    /**
     * Loads product by sku.
     *
     * @param string $sku
     * @return ProductInterface
     */
    protected function getProduct(string $sku): ProductInterface
    {
        return $this->productRepository->get(
            $sku,
            false,
            null,
            true
        );
    }

    /**
     * Prepares product page layout.
     *
     * @param ProductInterface $product
     * @return void
     */
    private function preparePageLayout(ProductInterface $product): void
    {
        $this->registerProduct($product);
        $this->page->addHandle([
            'default',
            'catalog_product_view',
        ]);
        $this->page->getLayout()->generateXml();
    }
}
