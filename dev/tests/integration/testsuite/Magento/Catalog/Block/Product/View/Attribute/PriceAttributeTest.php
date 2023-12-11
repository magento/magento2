<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Block\Product\View\Attribute;

use Magento\Directory\Model\PriceCurrency;

/**
 * Class checks price attribute displaying on frontend
 *
 * @magentoDbIsolation enabled
 * @magentoDataFixture Magento/Catalog/_files/product_decimal_attribute.php
 * @magentoDataFixture Magento/Catalog/_files/second_product_simple.php
 */
class PriceAttributeTest extends AbstractAttributeTest
{
    /** @var PriceCurrency */
    private $priceCurrency;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->priceCurrency = $this->objectManager->create(PriceCurrency::class);
    }

    /**
     * @dataProvider pricesDataProvider
     * @param string $price
     * @return void
     */
    public function testAttributeView(string $price): void
    {
        $this->processAttributeView('simple2', $price, $this->priceCurrency->convertAndFormat($price));
    }

    /**
     * @return array
     */
    public function pricesDataProvider(): array
    {
        return [
            'zero_price' => [
                'price' => '0',
            ],
            'positive_price' => [
                'price' => '150',
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
        return '';
    }
}
