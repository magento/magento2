<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogRule\Model\Indexer;

use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Setup\CategorySetup;
use Magento\Catalog\Test\Fixture\Category as CategoryFixture;
use Magento\Catalog\Test\Fixture\MultiselectAttribute as MultiselectAttributeFixture;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Catalog\Test\Fixture\SelectAttribute as SelectAttributeFixture;
use Magento\CatalogRule\Model\Indexer\IndexBuilder;
use Magento\CatalogRule\Model\ResourceModel\Rule as RuleResource;
use Magento\CatalogRule\Test\Fixture\Rule as CatalogRuleFixture;
use Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Negative catalog rule conditions: reindex and rule price for products without values.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
#[
    AppArea('adminhtml'),
    DbIsolation(true)
]
class NegativeConditionRulePriceTest extends TestCase
{
    private const WEBSITE_ID = 1;
    private const CUSTOMER_GROUP_ID = 1;
    private const PRODUCT_PRICE = 100.0;
    private const DISCOUNT_PERCENT = 50;
    private const EXPECTED_RULE_PRICE = 50.0;

    /**
     * @var ObjectManagerInterface
     */
    private ObjectManagerInterface $objectManager;

    /**
     * @var DataFixtureStorage
     */
    private DataFixtureStorage $fixtures;

    /**
     * @var RuleResource
     */
    private RuleResource $resourceRule;

    /**
     * @var IndexBuilder
     */
    private IndexBuilder $indexBuilder;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->fixtures = DataFixtureStorageManager::getStorage();
        $this->resourceRule = $this->objectManager->get(RuleResource::class);
        $this->indexBuilder = $this->objectManager->get(IndexBuilder::class);

        $this->objectManager->get(Product\ProductRuleProcessor::class)
            ->getIndexer()
            ->setScheduled(false);
        $this->objectManager->get(Rule\RuleProductProcessor::class)
            ->getIndexer()
            ->setScheduled(false);
    }

    /**
     * Multiselect "does not contain": empty product gets discount; product with option does not.
     */
    #[
        DataFixture(
            MultiselectAttributeFixture::class,
            [
                'entity_type_id' => CategorySetup::CATALOG_PRODUCT_ENTITY_TYPE_ID,
                'source_model' => null,
                'backend_model' => ArrayBackend::class,
                'is_used_for_promo_rules' => true,
                'attribute_model' => Attribute::class,
                'options' => ['option_a', 'option_b'],
            ],
            'attr'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'sku' => 'e2e-ms-empty',
                'price' => self::PRODUCT_PRICE,
            ],
            'product_empty'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'sku' => 'e2e-ms-has-a',
                'price' => self::PRODUCT_PRICE,
                'custom_attributes' => [
                    [
                        'attribute_code' => '$attr.attribute_code$',
                        'value' => '$attr.option_a$',
                    ],
                ],
            ],
            'product_excluded'
        ),
        DataFixture(
            CatalogRuleFixture::class,
            [
                'name' => 'E2E multiselect does not contain option_a',
                'is_active' => 1,
                'website_ids' => [self::WEBSITE_ID],
                'customer_group_ids' => [self::CUSTOMER_GROUP_ID],
                'simple_action' => 'by_percent',
                'discount_amount' => self::DISCOUNT_PERCENT,
                'stop_rules_processing' => false,
                'conditions' => [
                    [
                        'attribute' => '$attr.attribute_code$',
                        'operator' => '!{}',
                        'value' => '$attr.option_a$',
                    ],
                ],
            ],
            'rule'
        )
    ]
    public function testMultiselectDoesNotContainAppliesRulePriceToEmptyProduct(): void
    {
        $this->assertNegativeConditionRulePrices();
    }

    /**
     * Select "is not": empty product gets discount; product with excluded option does not.
     */
    #[
        DataFixture(
            SelectAttributeFixture::class,
            [
                'is_used_for_promo_rules' => true,
                'options' => ['option_a', 'option_b'],
            ],
            'attr'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'sku' => 'e2e-sel-empty',
                'price' => self::PRODUCT_PRICE,
            ],
            'product_empty'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'sku' => 'e2e-sel-has-a',
                'price' => self::PRODUCT_PRICE,
                'custom_attributes' => [
                    [
                        'attribute_code' => '$attr.attribute_code$',
                        'value' => '$attr.option_a$',
                    ],
                ],
            ],
            'product_excluded'
        ),
        DataFixture(
            CatalogRuleFixture::class,
            [
                'name' => 'E2E select is not option_a',
                'is_active' => 1,
                'website_ids' => [self::WEBSITE_ID],
                'customer_group_ids' => [self::CUSTOMER_GROUP_ID],
                'simple_action' => 'by_percent',
                'discount_amount' => self::DISCOUNT_PERCENT,
                'stop_rules_processing' => false,
                'conditions' => [
                    [
                        'attribute' => '$attr.attribute_code$',
                        'operator' => '!=',
                        'value' => '$attr.option_a$',
                    ],
                ],
            ],
            'rule'
        )
    ]
    public function testSelectIsNotAppliesRulePriceToEmptyProduct(): void
    {
        $this->assertNegativeConditionRulePrices();
    }

    /**
     * Category "is not": product without that category gets discount; product in it does not.
     */
    #[
        DataFixture(CategoryFixture::class, as: 'category'),
        DataFixture(
            ProductFixture::class,
            [
                'sku' => 'e2e-cat-empty',
                'price' => self::PRODUCT_PRICE,
            ],
            'product_empty'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'sku' => 'e2e-cat-in',
                'price' => self::PRODUCT_PRICE,
                'category_ids' => ['$category.id$'],
            ],
            'product_excluded'
        ),
        DataFixture(
            CatalogRuleFixture::class,
            [
                'name' => 'E2E category is not fixture category',
                'is_active' => 1,
                'website_ids' => [self::WEBSITE_ID],
                'customer_group_ids' => [self::CUSTOMER_GROUP_ID],
                'simple_action' => 'by_percent',
                'discount_amount' => self::DISCOUNT_PERCENT,
                'stop_rules_processing' => false,
                'conditions' => [
                    [
                        'attribute' => 'category_ids',
                        'operator' => '!=',
                        'value' => '$category.id$',
                    ],
                ],
            ],
            'rule'
        )
    ]
    public function testCategoryIsNotAppliesRulePriceToProductWithoutCategory(): void
    {
        $this->assertNegativeConditionRulePrices();
    }

    #[
        DataFixture(
            MultiselectAttributeFixture::class,
            [
                'entity_type_id' => CategorySetup::CATALOG_PRODUCT_ENTITY_TYPE_ID,
                'source_model' => null,
                'backend_model' => ArrayBackend::class,
                'is_used_for_promo_rules' => true,
                'attribute_model' => Attribute::class,
                'options' => ['option_a', 'option_b'],
            ],
            'attr'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'sku' => 'e2e-ms-undef-empty',
                'price' => self::PRODUCT_PRICE,
            ],
            'product_empty'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'sku' => 'e2e-ms-undef-has',
                'price' => self::PRODUCT_PRICE,
                'custom_attributes' => [
                    [
                        'attribute_code' => '$attr.attribute_code$',
                        'value' => '$attr.option_a$',
                    ],
                ],
            ],
            'product_excluded'
        ),
        DataFixture(
            CatalogRuleFixture::class,
            [
                'name' => 'E2E multiselect is undefined',
                'is_active' => 1,
                'website_ids' => [self::WEBSITE_ID],
                'customer_group_ids' => [self::CUSTOMER_GROUP_ID],
                'simple_action' => 'by_percent',
                'discount_amount' => self::DISCOUNT_PERCENT,
                'stop_rules_processing' => false,
                'conditions' => [
                    [
                        'attribute' => '$attr.attribute_code$',
                        'operator' => '<=>',
                        'value' => '',
                    ],
                ],
            ],
            'rule'
        )
    ]
    public function testMultiselectIsUndefinedAppliesRulePriceToEmptyProduct(): void
    {
        $this->assertNegativeConditionRulePrices();
    }

    #[
        DataFixture(
            MultiselectAttributeFixture::class,
            [
                'entity_type_id' => CategorySetup::CATALOG_PRODUCT_ENTITY_TYPE_ID,
                'source_model' => null,
                'backend_model' => ArrayBackend::class,
                'is_used_for_promo_rules' => true,
                'attribute_model' => Attribute::class,
                'options' => ['option_a', 'option_b'],
            ],
            'attr'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'sku' => 'e2e-ms-nin-empty',
                'price' => self::PRODUCT_PRICE,
            ],
            'product_empty'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'sku' => 'e2e-ms-nin-has-a',
                'price' => self::PRODUCT_PRICE,
                'custom_attributes' => [
                    [
                        'attribute_code' => '$attr.attribute_code$',
                        'value' => '$attr.option_a$',
                    ],
                ],
            ],
            'product_excluded'
        ),
        DataFixture(
            CatalogRuleFixture::class,
            [
                'name' => 'E2E multiselect is not one of option_a',
                'is_active' => 1,
                'website_ids' => [self::WEBSITE_ID],
                'customer_group_ids' => [self::CUSTOMER_GROUP_ID],
                'simple_action' => 'by_percent',
                'discount_amount' => self::DISCOUNT_PERCENT,
                'stop_rules_processing' => false,
                'conditions' => [
                    [
                        'attribute' => '$attr.attribute_code$',
                        'operator' => '!()',
                        'value' => '$attr.option_a$',
                    ],
                ],
            ],
            'rule'
        )
    ]
    public function testMultiselectIsNotOneOfAppliesRulePriceToEmptyProduct(): void
    {
        $this->assertNegativeConditionRulePrices();
    }

    private function assertNegativeConditionRulePrices(): void
    {
        $emptyProductId = (int)$this->fixtures->get('product_empty')->getId();
        $excludedProductId = (int)$this->fixtures->get('product_excluded')->getId();

        $this->indexBuilder->reindexByIds([$emptyProductId, $excludedProductId]);

        $date = new \DateTime();
        $emptyRulePrice = $this->resourceRule->getRulePrice(
            $date,
            self::WEBSITE_ID,
            self::CUSTOMER_GROUP_ID,
            $emptyProductId
        );
        $excludedRulePrice = $this->resourceRule->getRulePrice(
            $date,
            self::WEBSITE_ID,
            self::CUSTOMER_GROUP_ID,
            $excludedProductId
        );

        $this->assertEqualsWithDelta(
            self::EXPECTED_RULE_PRICE,
            (float)$emptyRulePrice,
            0.0001,
            'Product without the excluded value must receive the catalog rule price after reindex'
        );
        $this->assertFalse(
            $excludedRulePrice,
            'Product with the excluded value must not receive a catalog rule price'
        );
    }
}
