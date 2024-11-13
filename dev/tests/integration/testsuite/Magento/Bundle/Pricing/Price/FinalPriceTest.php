<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Pricing\Price;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * 'Final Price' model integration tests.
 *
 * @magentoDbIsolation disabled
 */
class FinalPriceTest extends TestCase
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
    }

    /**
     * Check minimal and maximal prices are calculated correctly for Bundle product selections with Tier Prices.
     *
     * @return void
     * @magentoDataFixture Magento/Bundle/_files/bundle_product_with_tier_price_selections.php
     */
    public function testGetPriceForBundleSelectionsWithTierPrices(): void
    {
        $priceModel = $this->getPriceModel('bundle_with_tier_price_selections');
        $this->assertEquals(15.0, $priceModel->getMinimalPrice()->getValue());
        $this->assertEquals(45.0, $priceModel->getMaximalPrice()->getValue());
    }

    /**
     * Create and retrieve Price Model for provided Product SKU.
     *
     * @param string $productSku
     * @return FinalPrice
     */
    private function getPriceModel(string $productSku): FinalPrice
    {
        $bundleProduct = $this->productRepository->get($productSku);

        return $this->objectManager->create(
            FinalPrice::class,
            [
                'saleableItem' => $bundleProduct,
                'quantity' => 0.,
            ]
        );
    }
}
