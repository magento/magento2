<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogRule\Model\ResourceModel\Product;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Catalog\Setup\CategorySetup;
use Magento\Catalog\Test\Fixture\MultiselectAttribute as MultiselectAttributeFixture;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Catalog\Test\Fixture\SelectAttribute as SelectAttributeFixture;
use Magento\CatalogRule\Model\ResourceModel\Product\ConditionsToCollectionApplier;
use Magento\CatalogRule\Model\Rule\Condition\Combine;
use Magento\CatalogRule\Model\Rule\Condition\CombineFactory;
use Magento\CatalogRule\Model\Rule\Condition\Product as ProductCondition;
use Magento\CatalogRule\Model\Rule\Condition\ProductFactory as ProductConditionFactory;
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
 * SQL-layer catalog rule filters for empty EAV attribute values (EavAttributeCondition path).
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
#[
    AppArea('adminhtml'),
    DbIsolation(true)
]
class NegativeEavConditionsToCollectionTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private ObjectManagerInterface $objectManager;

    /**
     * @var DataFixtureStorage
     */
    private DataFixtureStorage $fixtures;

    /**
     * @var ConditionsToCollectionApplier
     */
    private ConditionsToCollectionApplier $conditionsToCollectionApplier;

    /**
     * @var ProductCollectionFactory
     */
    private ProductCollectionFactory $productCollectionFactory;

    /**
     * @var CombineFactory
     */
    private CombineFactory $combineConditionFactory;

    /**
     * @var ProductConditionFactory
     */
    private ProductConditionFactory $productConditionFactory;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->fixtures = DataFixtureStorageManager::getStorage();
        $this->conditionsToCollectionApplier = $this->objectManager->get(ConditionsToCollectionApplier::class);
        $this->productCollectionFactory = $this->objectManager->get(ProductCollectionFactory::class);
        $this->combineConditionFactory = $this->objectManager->get(CombineFactory::class);
        $this->productConditionFactory = $this->objectManager->get(ProductConditionFactory::class);
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
        DataFixture(ProductFixture::class, ['sku' => 'sql-ms-empty'], 'product_empty'),
        DataFixture(
            ProductFixture::class,
            [
                'sku' => 'sql-ms-a',
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
                'sku' => 'sql-ms-b',
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
    public function testMultiselectDoesNotContainIncludesEmptyProductInCollection(): void
    {
        $skus = $this->getFilteredSkus(
            (string)$this->fixtures->get('attr')->getAttributeCode(),
            '!{}',
            (string)$this->fixtures->get('attr')->getData('option_a'),
            ['sql-ms-empty', 'sql-ms-a', 'sql-ms-b']
        );

        $this->assertContains('sql-ms-empty', $skus);
        $this->assertContains('sql-ms-b', $skus);
        $this->assertNotContains('sql-ms-a', $skus);
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
        DataFixture(ProductFixture::class, ['sku' => 'sql-ms-empty'], 'product_empty'),
        DataFixture(
            ProductFixture::class,
            [
                'sku' => 'sql-ms-a',
                'custom_attributes' => [
                    [
                        'attribute_code' => '$attr.attribute_code$',
                        'value' => '$attr.option_a$',
                    ],
                ],
            ],
            'product_a'
        )
    ]
    public function testMultiselectIsUndefinedIncludesOnlyEmptyProductInCollection(): void
    {
        $skus = $this->getFilteredSkus(
            (string)$this->fixtures->get('attr')->getAttributeCode(),
            '<=>',
            '',
            ['sql-ms-empty', 'sql-ms-a']
        );

        $this->assertContains('sql-ms-empty', $skus);
        $this->assertNotContains('sql-ms-a', $skus);
    }

    #[
        DataFixture(
            SelectAttributeFixture::class,
            [
                'is_used_for_promo_rules' => true,
                'options' => ['option_a', 'option_b'],
            ],
            'attr'
        ),
        DataFixture(ProductFixture::class, ['sku' => 'sql-sel-empty'], 'product_empty'),
        DataFixture(
            ProductFixture::class,
            [
                'sku' => 'sql-sel-a',
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
                'sku' => 'sql-sel-b',
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
    public function testSelectIsNotIncludesEmptyProductInCollection(): void
    {
        $skus = $this->getFilteredSkus(
            (string)$this->fixtures->get('attr')->getAttributeCode(),
            '!=',
            (string)$this->fixtures->get('attr')->getData('option_a'),
            ['sql-sel-empty', 'sql-sel-a', 'sql-sel-b']
        );

        $this->assertContains('sql-sel-empty', $skus);
        $this->assertContains('sql-sel-b', $skus);
        $this->assertNotContains('sql-sel-a', $skus);
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
        DataFixture(ProductFixture::class, ['sku' => 'sql-false-empty'], 'product_empty'),
        DataFixture(
            ProductFixture::class,
            [
                'sku' => 'sql-false-a',
                'custom_attributes' => [
                    [
                        'attribute_code' => '$attr.attribute_code$',
                        'value' => '$attr.option_a$',
                    ],
                ],
            ],
            'product_a'
        )
    ]
    public function testFalseIsUndefinedExcludesEmptyProductInCollection(): void
    {
        $attributeCode = (string)$this->fixtures->get('attr')->getAttributeCode();

        /** @var ProductCondition $undefinedCondition */
        $undefinedCondition = $this->productConditionFactory->create();
        $undefinedCondition->setType(ProductCondition::class);
        $undefinedCondition->setAttribute($attributeCode);
        $undefinedCondition->setOperator('<=>');
        $undefinedCondition->setValue('');

        /** @var Combine $falseCombine */
        $falseCombine = $this->combineConditionFactory->create();
        $falseCombine->setType(Combine::class);
        $falseCombine->setAggregator('all');
        $falseCombine->setValue(0);
        $falseCombine->setConditions([$undefinedCondition]);

        /** @var Combine $root */
        $root = $this->combineConditionFactory->create();
        $root->setType(Combine::class);
        $root->setAggregator('all');
        $root->setValue(1);
        $root->setConditions([$falseCombine]);

        $skus = $this->filterSkusByCondition($root, ['sql-false-empty', 'sql-false-a']);

        $this->assertNotContains('sql-false-empty', $skus);
        $this->assertContains('sql-false-a', $skus);
    }

    /**
     * @param string $attributeCode
     * @param string $operator
     * @param string $value
     * @param string[] $candidateSkus
     * @return string[]
     */
    private function getFilteredSkus(
        string $attributeCode,
        string $operator,
        string $value,
        array $candidateSkus
    ): array {
        /** @var ProductCondition $condition */
        $condition = $this->productConditionFactory->create();
        $condition->setType(ProductCondition::class);
        $condition->setAttribute($attributeCode);
        $condition->setOperator($operator);
        $condition->setValue($value);

        /** @var Combine $combine */
        $combine = $this->combineConditionFactory->create();
        $combine->setType(Combine::class);
        $combine->setAggregator('all');
        $combine->setValue(1);
        $combine->setConditions([$condition]);

        return $this->filterSkusByCondition($combine, $candidateSkus);
    }

    /**
     * @param Combine $condition
     * @param string[] $candidateSkus
     * @return string[]
     */
    private function filterSkusByCondition(Combine $condition, array $candidateSkus): array
    {
        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToSelect('sku');
        $collection->addFieldToFilter('sku', ['in' => $candidateSkus]);

        $filtered = $this->conditionsToCollectionApplier->applyConditionsToCollection($condition, $collection);

        return array_map(
            static function (Product $product): string {
                return (string)$product->getSku();
            },
            array_values($filtered->getItems())
        );
    }
}
