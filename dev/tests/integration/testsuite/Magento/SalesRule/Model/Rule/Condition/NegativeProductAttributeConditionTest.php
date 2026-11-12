<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Model\Rule\Condition;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Setup\CategorySetup;
use Magento\Catalog\Test\Fixture\MultiselectAttribute as MultiselectAttributeFixture;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Catalog\Test\Fixture\SelectAttribute as SelectAttributeFixture;
use Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend;
use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\SalesRule\Model\Rule\Condition\Product as SalesRuleProductCondition;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Sales rule product attribute negative conditions for empty attribute values.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
#[
    AppArea('frontend'),
    DbIsolation(true)
]
class NegativeProductAttributeConditionTest extends TestCase
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
     * @var ProductRepositoryInterface
     */
    private ProductRepositoryInterface $productRepository;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->fixtures = DataFixtureStorageManager::getStorage();
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
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
                'sku' => 'sr-ms-empty',
                'price' => 100,
            ],
            'product_empty'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'sku' => 'sr-ms-a',
                'price' => 100,
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
    public function testMultiselectDoesNotContainAppliesToProductWithoutValue(): void
    {
        $attributeCode = (string)$this->fixtures->get('attr')->getAttributeCode();
        $optionA = (string)$this->fixtures->get('attr')->getData('option_a');

        $this->assertTrue(
            $this->validateProductCondition('product_empty', $attributeCode, '!{}', $optionA),
            'Product without multiselect value must match "does not contain"'
        );
        $this->assertFalse(
            $this->validateProductCondition('product_a', $attributeCode, '!{}', $optionA),
            'Product with excluded multiselect option must not match "does not contain"'
        );
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
        DataFixture(
            ProductFixture::class,
            [
                'sku' => 'sr-sel-empty',
                'price' => 100,
            ],
            'product_empty'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'sku' => 'sr-sel-a',
                'price' => 100,
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
    public function testSelectIsNotAppliesToProductWithoutValue(): void
    {
        $attributeCode = (string)$this->fixtures->get('attr')->getAttributeCode();
        $optionA = (string)$this->fixtures->get('attr')->getData('option_a');

        $this->assertTrue(
            $this->validateProductCondition('product_empty', $attributeCode, '!=', $optionA),
            'Product without select value must match "is not"'
        );
        $this->assertFalse(
            $this->validateProductCondition('product_a', $attributeCode, '!=', $optionA),
            'Product with excluded select option must not match "is not"'
        );
    }

    /**
     * @param string $productFixtureName
     * @param string $attributeCode
     * @param string $operator
     * @param string $value
     * @return bool
     */
    private function validateProductCondition(
        string $productFixtureName,
        string $attributeCode,
        string $operator,
        string $value
    ): bool {
        $productId = (int)$this->fixtures->get($productFixtureName)->getId();
        $product = $this->productRepository->getById($productId, false, null, true);
        $product->load($productId);

        /** @var QuoteItem $quoteItem */
        $quoteItem = $this->objectManager->create(QuoteItem::class);
        $quoteItem->setProduct($product);
        $quoteItem->setQty(1);

        /** @var SalesRuleProductCondition $condition */
        $condition = $this->objectManager->create(SalesRuleProductCondition::class);
        $condition->setAttribute($attributeCode);
        $condition->setOperator($operator);
        $condition->setValue($value);

        return (bool)$condition->validate($quoteItem);
    }
}
