<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogRule\Model\Rule;

use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Setup\CategorySetup;
use Magento\Catalog\Test\Fixture\MultiselectAttribute as MultiselectAttributeFixture;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\CatalogRule\Model\Rule;
use Magento\CatalogRule\Model\Rule\Condition\Combine;
use Magento\CatalogRule\Model\Rule\Condition\Product as ProductCondition;
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
 * Multiselect "is undefined" for catalog rules — mitigation for empty-value matching under negatives.
 *
 * "Is defined" is not a separate operator: use a FALSE combine over "is undefined".
 */
#[
    AppArea('adminhtml'),
    DbIsolation(true)
]
class DefinedUndefinedConditionsTest extends TestCase
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
     * Multiselect "is undefined" matches products without a value.
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
        DataFixture(ProductFixture::class, ['sku' => 'undef-ms-empty'], 'product_empty'),
        DataFixture(
            ProductFixture::class,
            [
                'sku' => 'undef-ms-has',
                'custom_attributes' => [
                    [
                        'attribute_code' => '$attr.attribute_code$',
                        'value' => '$attr.option_a$',
                    ],
                ],
            ],
            'product_defined'
        ),
        DataFixture(
            CatalogRuleFixture::class,
            [
                'name' => 'Multiselect is undefined',
                'is_active' => 1,
                'website_ids' => [1],
                'customer_group_ids' => [0, 1],
                'simple_action' => 'by_percent',
                'discount_amount' => 10,
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
    public function testMultiselectIsUndefinedMatchesEmptyProductOnly(): void
    {
        $matchingIds = $this->getMatchingProductIds('rule');
        $this->assertProductMatches($matchingIds, 'product_empty');
        $this->assertProductDoesNotMatch($matchingIds, 'product_defined');
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
        DataFixture(ProductFixture::class, ['sku' => 'combo-ms-empty'], 'product_empty'),
        DataFixture(
            ProductFixture::class,
            [
                'sku' => 'combo-ms-a',
                'custom_attributes' => [
                    [
                        'attribute_code' => '$attr.attribute_code$',
                        'value' => '$attr.option_a$',
                    ],
                ],
            ],
            'product_a'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'sku' => 'combo-ms-b',
                'custom_attributes' => [
                    [
                        'attribute_code' => '$attr.attribute_code$',
                        'value' => '$attr.option_b$',
                    ],
                ],
            ],
            'product_b'
        )
    ]
    public function testIsNotWithFalseIsUndefinedExcludesEmptyProducts(): void
    {
        $attr = $this->fixtures->get('attr');
        $attributeCode = $attr->getAttributeCode();
        $optionA = $attr->getData('option_a');

        /** @var Rule $rule */
        $rule = $this->objectManager->get(\Magento\CatalogRule\Model\RuleFactory::class)->create();
        $rule->loadPost([
            'name' => 'Is not A and not undefined',
            'is_active' => '1',
            'stop_rules_processing' => 0,
            'website_ids' => [1],
            'customer_group_ids' => [0, 1],
            'discount_amount' => 10,
            'simple_action' => 'by_percent',
            'from_date' => '',
            'to_date' => '',
            'sort_order' => 0,
            'conditions' => [
                '1' => [
                    'type' => Combine::class,
                    'aggregator' => 'all',
                    'value' => '1',
                    'new_child' => '',
                ],
                '1--1' => [
                    'type' => ProductCondition::class,
                    'attribute' => $attributeCode,
                    'operator' => '!()',
                    'value' => $optionA,
                ],
                '1--2' => [
                    'type' => Combine::class,
                    'aggregator' => 'all',
                    'value' => '0',
                    'new_child' => '',
                ],
                '1--2--1' => [
                    'type' => ProductCondition::class,
                    'attribute' => $attributeCode,
                    'operator' => '<=>',
                    'value' => '',
                ],
            ],
        ]);
        $this->objectManager->get(\Magento\CatalogRule\Api\CatalogRuleRepositoryInterface::class)->save($rule);

        $productIds = [
            (int)$this->fixtures->get('product_empty')->getId(),
            (int)$this->fixtures->get('product_a')->getId(),
            (int)$this->fixtures->get('product_b')->getId(),
        ];
        $rule->setProductsFilter($productIds);
        $matchingIds = $rule->getMatchingProductIds();

        $this->assertProductDoesNotMatch(
            $matchingIds,
            'product_empty',
            'Empty products must be excluded when is undefined is inverted via a FALSE combine'
        );
        $this->assertProductDoesNotMatch($matchingIds, 'product_a');
        $this->assertProductMatches($matchingIds, 'product_b');
    }

    /**
     * @param string $ruleFixtureName
     * @return array<int, array<int, bool>>
     */
    private function getMatchingProductIds(string $ruleFixtureName): array
    {
        $ruleData = $this->fixtures->get($ruleFixtureName);
        /** @var Rule $rule */
        $rule = $this->objectManager->create(Rule::class);
        $rule->load($ruleData->getId());
        $this->assertNotEmpty($rule->getId());

        $productIds = [];
        foreach (['product_empty', 'product_defined', 'product_a', 'product_b'] as $name) {
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
    private function assertProductMatches(
        array $matchingIds,
        string $productFixtureName,
        string $message = ''
    ): void {
        $productId = (int)$this->fixtures->get($productFixtureName)->getId();
        $this->assertArrayHasKey($productId, $matchingIds, $message ?: "Product {$productFixtureName} should match");
        $this->assertNotEmpty(
            array_filter($matchingIds[$productId]),
            $message ?: "Product {$productFixtureName} should match for a website"
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
        string $message = ''
    ): void {
        $productId = (int)$this->fixtures->get($productFixtureName)->getId();
        if (!isset($matchingIds[$productId])) {
            $this->assertTrue(true);
            return;
        }
        $this->assertEmpty(
            array_filter($matchingIds[$productId]),
            $message ?: "Product {$productFixtureName} should not match"
        );
    }
}
