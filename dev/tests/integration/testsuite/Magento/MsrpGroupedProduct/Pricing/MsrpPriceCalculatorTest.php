<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MsrpGroupedProduct\Pricing;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test group product minimum advertised price model
 */
class MsrpPriceCalculatorTest extends TestCase
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;
    /**
     * @var MsrpPriceCalculator
     */
    private $model;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $objectManager = Bootstrap::getObjectManager();
        $this->productRepository = $objectManager->get(ProductRepositoryInterface::class);
        $this->model = $objectManager->get(MsrpPriceCalculator::class);
    }

    /**
     * Test grouped product minimum advertised price
     *
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/GroupedProduct/_files/product_grouped.php
     * @dataProvider getMsrpPriceValueDataProvider
     * @param float|null $simpleProductPriceMsrp
     * @param float|null $virtualProductMsrp
     * @param float|null $expectedMsrp
     */
    public function testGetMsrpPriceValue(
        ?float $simpleProductPriceMsrp,
        ?float $virtualProductMsrp,
        ?float $expectedMsrp
    ): void {
        $this->setProductMinimumAdvertisedPrice('simple', $simpleProductPriceMsrp);
        $this->setProductMinimumAdvertisedPrice('virtual-product', $virtualProductMsrp);
        $groupedProduct = $this->getProduct('grouped-product');
        $this->assertEquals($expectedMsrp, $this->model->getMsrpPriceValue($groupedProduct));
    }

    /**
     * Set product minimum advertised price by sku
     *
     * @param string $sku
     * @param float|null $msrp
     */
    private function setProductMinimumAdvertisedPrice(string $sku, ?float $msrp): void
    {
        $product = $this->getProduct($sku);
        $product->setMsrp($msrp);
        $this->productRepository->save($product);
    }

    /**
     * Get product by sku
     *
     * @param string $sku
     * @return ProductInterface
     */
    private function getProduct(string $sku): ProductInterface
    {
        return $this->productRepository->get($sku, false, null, true);
    }

    /**
     * @return array
     */
    public static function getMsrpPriceValueDataProvider(): array
    {
        return [
            [
                12.0,
                8.0,
                8.0
            ],
            [
                12.0,
                null,
                12.0
            ],
            [
                null,
                null,
                0.0
            ]
        ];
    }
}
