<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogRule\Model\Rule;

use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Setup\CategorySetup;
use Magento\Catalog\Test\Fixture\Category as CategoryFixture;
use Magento\Catalog\Test\Fixture\MultiselectAttribute as MultiselectAttributeFixture;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Catalog\Test\Fixture\SelectAttribute as SelectAttributeFixture;
use Magento\CatalogRule\Model\Rule;
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
 * Catalog price rule negative conditions for products with no attribute/category value.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
#[
    AppArea('adminhtml'),
    DbIsolation(true)
]
class MatchingProductIdsNegativeConditionsTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private ObjectManagerInterface $objectManager;

    /**
     * @var DataFixtureStorage
     */
    private DataFixtureStorage $fixtures;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->fixtures = DataFixtureStorageManager::getStorage();
    }

    /**
     * Multiselect "does not contain" must include products that never had the attribute set.
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
                'sku' => 'ms-no-value',
                'custom_attributes' => [
                    // attribute intentionally unset — no EAV row
                ],
            ],
            'product_no_value'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'sku' => 'ms-has-a',
                'custom_attributes' => [
                    [
                        'attribute_code' => '$attr.attribute_code$',
                        'value' => '$attr.option_a$',
                    ],
                ],
            ],
            'product_has_a'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'sku' => 'ms-has-b',
                'custom_attributes' => [
                    [
                        'attribute_code' => '$attr.attribute_code$',
                        'value' => '$attr.option_b$',
                    ],
                ],
            ],
            'product_has_b'
        ),
        DataFixture(
            CatalogRuleFixture::class,
            [
                'name' => 'Multiselect does not contain option_a',
                'is_active' => 1,
                'website_ids' => [1],
                'customer_group_ids' => [0, 1],
                'simple_action' => 'by_percent',
                'discount_amount' => 10,
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
    public function testMultiselectDoesNotContainIncludesProductsWithoutValue(): void
    {
        $matchingIds = $this->getMatchingProductIds('rule');

        $this->assertProductMatches(
            $matchingIds,
            'product_no_value',
            'Product without multiselect value must match "does not contain"'
        );
        $this->assertProductMatches(
            $matchingIds,
            'product_has_b',
            'Product with a different multiselect option must match "does not contain"'
        );
        $this->assertProductDoesNotMatch(
            $matchingIds,
            'product_has_a',
            'Product with the excluded multiselect option must not match "does not contain"'
        );
    }

    /**
     * Multiselect "is not one of" must include products that never had the attribute set.
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
            ['sku' => 'ms2-no-value'],
            'product_no_value'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'sku' => 'ms2-has-a',
                'custom_attributes' => [
                    [
                        'attribute_code' => '$attr.attribute_code$',
                        'value' => '$attr.option_a$',
                    ],
                ],
            ],
            'product_has_a'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'sku' => 'ms2-has-b',
                'custom_attributes' => [
                    [
                        'attribute_code' => '$attr.attribute_code$',
                        'value' => '$attr.option_b$',
                    ],
                ],
            ],
            'product_has_b'
        ),
        DataFixture(
            CatalogRuleFixture::class,
            [
                'name' => 'Multiselect is not one of option_a',
                'is_active' => 1,
                'website_ids' => [1],
                'customer_group_ids' => [0, 1],
                'simple_action' => 'by_percent',
                'discount_amount' => 10,
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
    public function testMultiselectIsNotOneOfIncludesProductsWithoutValue(): void
    {
        $matchingIds = $this->getMatchingProductIds('rule');

        $this->assertProductMatches(
            $matchingIds,
            'product_no_value',
            'Product without multiselect value must match "is not one of"'
        );
        $this->assertProductMatches(
            $matchingIds,
            'product_has_b',
            'Product with a different multiselect option must match "is not one of"'
        );
        $this->assertProductDoesNotMatch(
            $matchingIds,
            'product_has_a',
            'Product with the excluded multiselect option must not match "is not one of"'
        );
    }

    /**
     * Dropdown "is not" must include products that never had the attribute set.
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
            ['sku' => 'sel-no-value'],
            'product_no_value'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'sku' => 'sel-has-a',
                'custom_attributes' => [
                    [
                        'attribute_code' => '$attr.attribute_code$',
                        'value' => '$attr.option_a$',
                    ],
                ],
            ],
            'product_has_a'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'sku' => 'sel-has-b',
                'custom_attributes' => [
                    [
                        'attribute_code' => '$attr.attribute_code$',
                        'value' => '$attr.option_b$',
                    ],
                ],
            ],
            'product_has_b'
        ),
        DataFixture(
            CatalogRuleFixture::class,
            [
                'name' => 'Select is not option_a',
                'is_active' => 1,
                'website_ids' => [1],
                'customer_group_ids' => [0, 1],
                'simple_action' => 'by_percent',
                'discount_amount' => 10,
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
    public function testSelectIsNotIncludesProductsWithoutValue(): void
    {
        $matchingIds = $this->getMatchingProductIds('rule');

        $this->assertProductMatches(
            $matchingIds,
            'product_no_value',
            'Product without select value must match "is not"'
        );
        $this->assertProductMatches(
            $matchingIds,
            'product_has_b',
            'Product with a different select option must match "is not"'
        );
        $this->assertProductDoesNotMatch(
            $matchingIds,
            'product_has_a',
            'Product with the excluded select option must not match "is not"'
        );
    }

    /**
     * Category "is not one of" must include products that are not assigned to that category,
     * including products with no category assignment at all.
     */
    #[
        DataFixture(CategoryFixture::class, as: 'category'),
        DataFixture(
            ProductFixture::class,
            ['sku' => 'cat-no-category'],
            'product_no_category'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'sku' => 'cat-in-category',
                'category_ids' => ['$category.id$'],
            ],
            'product_in_category'
        ),
        DataFixture(
            CatalogRuleFixture::class,
            [
                'name' => 'Category is not one of fixture category',
                'is_active' => 1,
                'website_ids' => [1],
                'customer_group_ids' => [0, 1],
                'simple_action' => 'by_percent',
                'discount_amount' => 10,
                'conditions' => [
                    [
                        'attribute' => 'category_ids',
                        'operator' => '!()',
                        'value' => '$category.id$',
                    ],
                ],
            ],
            'rule'
        )
    ]
    public function testCategoryIsNotOneOfIncludesProductsWithoutCategory(): void
    {
        $matchingIds = $this->getMatchingProductIds('rule');

        $this->assertProductMatches(
            $matchingIds,
            'product_no_category',
            'Product without category assignment must match "category is not one of"'
        );
        $this->assertProductDoesNotMatch(
            $matchingIds,
            'product_in_category',
            'Product assigned to the excluded category must not match'
        );
    }

    /**
     * Category "is not" must include products that are not assigned to that category,
     * including products with no category assignment at all.
     */
    #[
        DataFixture(CategoryFixture::class, as: 'category'),
        DataFixture(
            ProductFixture::class,
            ['sku' => 'cat2-no-category'],
            'product_no_category'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'sku' => 'cat2-in-category',
                'category_ids' => ['$category.id$'],
            ],
            'product_in_category'
        ),
        DataFixture(
            CatalogRuleFixture::class,
            [
                'name' => 'Category is not fixture category',
                'is_active' => 1,
                'website_ids' => [1],
                'customer_group_ids' => [0, 1],
                'simple_action' => 'by_percent',
                'discount_amount' => 10,
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
    public function testCategoryIsNotIncludesProductsWithoutCategory(): void
    {
        $matchingIds = $this->getMatchingProductIds('rule');

        $this->assertProductMatches(
            $matchingIds,
            'product_no_category',
            'Product without category assignment must match "category is not"'
        );
        $this->assertProductDoesNotMatch(
            $matchingIds,
            'product_in_category',
            'Product assigned to the excluded category must not match'
        );
    }

    /**
     * special_price "is not" / absence: product with no special_price must match a negative
     * comparison that excludes a concrete special_price value (issue comment scenario).
     */
    #[
        DataFixture(
            ProductFixture::class,
            [
                'sku' => 'sp-no-special',
                'price' => 100,
                // special_price left unset
            ],
            'product_no_special'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'sku' => 'sp-has-special',
                'price' => 100,
                'special_price' => 25,
            ],
            'product_has_special'
        ),
        DataFixture(
            CatalogRuleFixture::class,
            [
                'name' => 'special_price is not 25',
                'is_active' => 1,
                'website_ids' => [1],
                'customer_group_ids' => [0, 1],
                'simple_action' => 'by_percent',
                'discount_amount' => 10,
                'conditions' => [
                    [
                        'attribute' => 'special_price',
                        'operator' => '!=',
                        'value' => '25',
                    ],
                ],
            ],
            'rule'
        )
    ]
    public function testSpecialPriceIsNotIncludesProductsWithoutSpecialPrice(): void
    {
        $matchingIds = $this->getMatchingProductIds('rule');

        $this->assertProductMatches(
            $matchingIds,
            'product_no_special',
            'Product without special_price must match "special_price is not 25"'
        );
        $this->assertProductDoesNotMatch(
            $matchingIds,
            'product_has_special',
            'Product with special_price = 25 must not match "special_price is not 25"'
        );
    }

    /**
     * Load rule and return matching product IDs keyed by product ID.
     *
     * @param string $ruleFixtureName
     * @return array<int, array<int, bool>>
     */
    private function getMatchingProductIds(string $ruleFixtureName): array
    {
        $ruleData = $this->fixtures->get($ruleFixtureName);
        /** @var Rule $rule */
        $rule = $this->objectManager->create(Rule::class);
        $rule->load($ruleData->getId());
        $this->assertNotEmpty($rule->getId(), 'Catalog rule fixture must be saved');

        // Restrict matching to fixtures under test so unrelated catalog products do not affect assertions.
        $productIds = [];
        foreach (['product_no_value', 'product_has_a', 'product_has_b', 'product_no_category',
            'product_in_category', 'product_no_special', 'product_has_special'] as $name) {
            $product = $this->fixtures->get($name);
            if ($product !== null) {
                $productIds[] = (int)$product->getId();
            }
        }
        $rule->setProductsFilter($productIds);

        return $rule->getMatchingProductIds();
    }

    /**
     * @param array<int, array<int, bool>> $matchingIds
     * @param string $productFixtureName
     * @param string $message
     */
    private function assertProductMatches(array $matchingIds, string $productFixtureName, string $message): void
    {
        $productId = (int)$this->fixtures->get($productFixtureName)->getId();
        $this->assertArrayHasKey(
            $productId,
            $matchingIds,
            $message . sprintf(' (product id %d missing from matching set)', $productId)
        );
        $this->assertNotEmpty(
            array_filter($matchingIds[$productId]),
            $message . sprintf(' (product id %d present but not matched for any website)', $productId)
        );
    }

    /**
     * @param array<int, array<int, bool>> $matchingIds
     * @param string $productFixtureName
     * @param string $message
     */
    private function assertProductDoesNotMatch(
        array $matchingIds,
        string $productFixtureName,
        string $message
    ): void {
        $productId = (int)$this->fixtures->get($productFixtureName)->getId();
        if (!isset($matchingIds[$productId])) {
            $this->assertTrue(true);
            return;
        }
        $this->assertEmpty(
            array_filter($matchingIds[$productId]),
            $message . sprintf(' (product id %d matched for websites: %s)', $productId, json_encode($matchingIds[$productId]))
        );
    }
}
