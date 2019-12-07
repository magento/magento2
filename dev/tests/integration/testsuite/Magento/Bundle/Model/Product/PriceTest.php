<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Model\Product;

/**
 * Class to test bundle prices
 */
class PriceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Bundle\Model\Product\Price
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Bundle\Model\Product\Price::class
        );
    }

    /**
     * @magentoDataFixture Magento/Bundle/_files/product_with_tier_pricing.php
     */
    public function testGetTierPrice()
    {
        /** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
        $productRepository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $product = $productRepository->get('bundle-product');
        // fixture

        // Note that this is really not the "tier price" but the "tier discount percentage"
        // so it is expected to be increasing instead of decreasing
        $this->assertEquals(8.0, $this->_model->getTierPrice(2, $product));
        $this->assertEquals(20.0, $this->_model->getTierPrice(3, $product));
        $this->assertEquals(20.0, $this->_model->getTierPrice(4, $product));
        $this->assertEquals(30.0, $this->_model->getTierPrice(5, $product));
    }

    /**
     * Test calculation final price for bundle product with tire price in simple product
     *
     * @param float $bundleQty
     * @param float $selectionQty
     * @param float $finalPrice
     * @magentoDataFixture Magento/Bundle/_files/product_with_simple_tier_pricing.php
     * @dataProvider getSelectionFinalTotalPriceWithSimpleTierPriceDataProvider
     */
    public function testGetSelectionFinalTotalPriceWithSimpleTierPrice(
        float $bundleQty,
        float $selectionQty,
        float $finalPrice
    ) {
        /** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
        $productRepository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $bundleProduct = $productRepository->get('bundle-product');
        $simpleProduct = $productRepository->get('simple');
        $simpleProduct->setCustomerGroupId(\Magento\Customer\Model\Group::CUST_GROUP_ALL);

        $this->assertEquals(
            $finalPrice,
            $this->_model->getSelectionFinalTotalPrice(
                $bundleProduct,
                $simpleProduct,
                $bundleQty,
                $selectionQty,
                false
            ),
            'Tier price calculation for Simple product is wrong'
        );
    }

    /**
     * @return array
     */
    public function getSelectionFinalTotalPriceWithSimpleTierPriceDataProvider(): array
    {
        return [
            [1, 1, 10],
            [2, 1, 8],
            [5, 1, 5],
        ];
    }
}
