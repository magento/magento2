<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogWidget\Block\Product;

use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Setup\CategorySetup;
use Magento\Catalog\Test\Fixture\MultiselectAttribute as MultiselectAttributeFixture;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\CatalogWidget\Block\Product\ProductsList;
use Magento\CatalogWidget\Model\Rule\Condition\Combine;
use Magento\CatalogWidget\Model\Rule\Condition\Product as WidgetProductCondition;
use Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Catalog product list widget filters for empty multiselect values (Sql Builder path).
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
#[
    DbIsolation(false)
]
class NegativeMultiselectConditionsTest extends TestCase
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
     * @var ProductsList
     */
    private ProductsList $block;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->fixtures = DataFixtureStorageManager::getStorage();
        $this->block = $this->objectManager->create(ProductsList::class);
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
        DataFixture(ProductFixture::class, ['sku' => 'widget-ms-empty'], 'product_empty'),
        DataFixture(
            ProductFixture::class,
            [
                'sku' => 'widget-ms-a',
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
                'sku' => 'widget-ms-b',
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
    public function testDoesNotContainIncludesProductsWithoutMultiselectValue(): void
    {
        $attributeCode = (string)$this->fixtures->get('attr')->getAttributeCode();
        $optionA = (string)$this->fixtures->get('attr')->getData('option_a');

        $skus = $this->getCollectionSkus([
            '1' => [
                'type' => Combine::class,
                'aggregator' => 'all',
                'value' => '1',
                'new_child' => '',
            ],
            '1--1' => [
                'type' => WidgetProductCondition::class,
                'attribute' => $attributeCode,
                'operator' => '!{}',
                'value' => $optionA,
            ],
        ]);

        $this->assertContains('widget-ms-empty', $skus);
        $this->assertContains('widget-ms-b', $skus);
        $this->assertNotContains('widget-ms-a', $skus);
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
        DataFixture(ProductFixture::class, ['sku' => 'widget-undef-empty'], 'product_empty'),
        DataFixture(
            ProductFixture::class,
            [
                'sku' => 'widget-undef-a',
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
    public function testIsUndefinedIncludesOnlyProductsWithoutMultiselectValue(): void
    {
        $attributeCode = (string)$this->fixtures->get('attr')->getAttributeCode();

        $skus = $this->getCollectionSkus([
            '1' => [
                'type' => Combine::class,
                'aggregator' => 'all',
                'value' => '1',
                'new_child' => '',
            ],
            '1--1' => [
                'type' => WidgetProductCondition::class,
                'attribute' => $attributeCode,
                'operator' => '<=>',
                'value' => '',
            ],
        ]);

        $this->assertContains('widget-undef-empty', $skus);
        $this->assertNotContains('widget-undef-a', $skus);
    }

    /**
     * @param array<string, array<string, mixed>> $conditions
     * @return string[]
     */
    private function getCollectionSkus(array $conditions): array
    {
        $candidateIds = [];
        foreach (['product_empty', 'product_a', 'product_b'] as $fixtureName) {
            $product = $this->fixtures->get($fixtureName);
            if ($product !== null) {
                $candidateIds[] = (int)$product->getId();
            }
        }

        $this->block->setConditions($conditions);
        $collection = $this->block->createCollection();
        $collection->addFieldToFilter('entity_id', ['in' => $candidateIds]);
        $collection->load();

        return $collection->getColumnValues('sku');
    }
}
