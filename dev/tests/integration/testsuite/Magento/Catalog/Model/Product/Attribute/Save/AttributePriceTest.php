<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Product\Attribute\Save;

use Magento\Eav\Model\Entity\Attribute\Exception;

/**
 * @magentoDbIsolation enabled
 * @magentoDataFixture Magento/Catalog/_files/product_decimal_attribute.php
 * @magentoDataFixture Magento/Catalog/_files/second_product_simple.php
 */
class AttributePriceTest extends AbstractAttributeTest
{
    /**
     * @magentoDataFixture Magento/Catalog/_files/product_decimal_attribute.php
     * @magentoDataFixture Magento/Catalog/_files/second_product_simple.php
     * @magentoDataFixture Magento/Catalog/_files/product_simple_out_of_stock.php
     * @dataProvider uniqueAttributeValueProvider
     * @inheritdoc
     */
    public function testUniqueAttribute(string $firstSku, string $secondSku): void
    {
        $this->markTestSkipped('Test is blocked by issue MC-29018');
        parent::testUniqueAttribute($firstSku, $secondSku);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_decimal_attribute.php
     * @magentoDataFixture Magento/Catalog/_files/second_product_simple.php
     * @return void
     */
    public function testNegativeValue(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage((string)__('Please enter a number 0 or greater in this field.'));
        $this->setAttributeValueAndValidate('simple2', '-1');
    }

    /**
     * @dataProvider productProvider
     * @param string $productSku
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function testDefaultValue(string $productSku): void
    {
        // product price attribute does not support default value
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

    /**
     * @inheritdoc
     */
    protected function getAttributeCode(): string
    {
        return 'decimal_attribute';
    }

    /**
     * @inheritdoc
     */
    protected function getDefaultAttributeValue(): string
    {
        return '100.000000';
    }
}
