<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Product\Attribute\Save;

/**
 * @magentoDbIsolation enabled
 * @magentoDataFixture Magento/Swatches/_files/product_text_swatch_attribute.php
 * @magentoDataFixture Magento/Catalog/_files/second_product_simple.php
 */
class AttributeTextSwatchTest extends AbstractAttributeTest
{
    /**
     * @inheritdoc
     */
    protected function getAttributeCode(): string
    {
        return 'text_swatch_attribute';
    }

    /**
     * @inheritdoc
     */
    protected function getDefaultAttributeValue(): string
    {
        return $this->attribute->getSource()->getOptionId('Option 2');
    }

    /**
     * @magentoDataFixture Magento/Swatches/_files/product_text_swatch_attribute.php
     * @magentoDataFixture Magento/Catalog/_files/second_product_simple.php
     * @magentoDataFixture Magento/Catalog/_files/product_simple_out_of_stock.php
     * @dataProvider uniqueTestProvider
     * phpcs:disable Generic.CodeAnalysis.UselessOverridingMethod
     * @inheritdoc
     */
    public function testUniqueAttribute(string $firstSku, string $secondSku): void
    {
        parent::testUniqueAttribute($firstSku, $secondSku);
    }

    /**
     * @inheritdoc
     */
    public function productProvider(): array
    {
        return [
            [
                'product_sku' => 'simple2',
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function uniqueTestProvider(): array
    {
        return [
            [
                'first_product_sku' => 'simple2',
                'second_product_sku' => 'simple-out-of-stock',
            ],
        ];
    }
}
