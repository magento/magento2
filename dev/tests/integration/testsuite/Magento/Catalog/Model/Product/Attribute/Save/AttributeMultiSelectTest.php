<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Product\Attribute\Save;

use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @magentoDbIsolation enabled
 * @magentoDataFixture  Magento/Catalog/_files/multiselect_attribute.php
 * @magentoDataFixture Magento/Catalog/_files/second_product_simple.php
 */
class AttributeMultiSelectTest extends AbstractAttributeTest
{
    /**
     * @inheritdoc
     */
    protected function getAttributeCode(): string
    {
        return 'multiselect_attribute';
    }

    /**
     * @inheritdoc
     */
    protected function getDefaultAttributeValue(): string
    {
        return $this->getAttribute()->getSource()->getOptionId('Option 1');
    }

    /**
     * @inheritdoc
     */
    #[DataProvider('productProvider')]
    public function testDefaultValue(string $productSku): void
    {
        $this->markTestSkipped('Test is blocked by issue MC-29019');
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/multiselect_attribute.php
     * @magentoDataFixture Magento/Catalog/_files/second_product_simple.php
     * @magentoDataFixture Magento/Catalog/_files/product_simple_out_of_stock.php
     * phpcs:disable Generic.CodeAnalysis.UselessOverridingMethod
     * @inheritdoc
     */
    #[DataProvider('uniqueAttributeValueProvider')]
    public function testUniqueAttribute(string $firstSku, string $secondSku): void
    {
        parent::testUniqueAttribute($firstSku, $secondSku);
    }

    /**
     * @inheritdoc
     */
    public static function productProvider(): array
    {
        return [
            [
                'productSku' => 'simple2',
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function uniqueAttributeValueProvider(): array
    {
        return [
            [
                'firstSku' => 'simple2',
                'secondSku' => 'simple-out-of-stock',
            ],
        ];
    }
}
