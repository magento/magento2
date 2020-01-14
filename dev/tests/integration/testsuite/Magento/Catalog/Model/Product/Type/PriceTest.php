<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Product\Type;

use Magento\Catalog\Api\Data\ProductCustomOptionInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Option;
use Magento\Customer\Model\Session;
use Magento\Framework\DataObject;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Simple product price test.
 *
 * @magentoDataFixture Magento/Catalog/_files/product_simple.php
 * @magentoDbIsolation enabled
 */
class PriceTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Price
     */
    private $productPrice;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->productPrice = $this->objectManager->create(Price::class);
        $this->productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
        $this->customerSession = $this->objectManager->get(Session::class);
    }

    /**
     * Assert that for logged user product price equal to price from catalog rule.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDataFixture Magento/CatalogRule/_files/catalog_rule_6_off_logged_user.php
     * @magentoDataFixture Magento/Customer/_files/customer.php
     *
     * @magentoDbIsolation disabled
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     *
     * @return void
     */
    public function testPriceByRuleForLoggedUser(): void
    {
        $product = $this->productRepository->get('simple');
        $this->assertEquals(10, $this->productPrice->getFinalPrice(1, $product));
        $this->customerSession->setCustomerId(1);
        try {
            $this->assertEquals(4, $this->productPrice->getFinalPrice(1, $product));
        } finally {
            $this->customerSession->setCustomerId(null);
        }
    }

    /**
     * Assert price for different customer groups.
     *
     * @magentoDataFixture Magento/Catalog/_files/simple_product_with_tier_price_for_logged_user.php
     * @magentoDataFixture Magento/Customer/_files/customer.php
     *
     * @magentoAppIsolation enabled
     *
     * @return void
     */
    public function testTierPriceWithDifferentCustomerGroups(): void
    {
        $product = $this->productRepository->get('simple');
        $this->assertEquals(8, $this->productPrice->getFinalPrice(2, $product));
        $this->assertEquals(5, $this->productPrice->getFinalPrice(3, $product));
        $this->customerSession->setCustomerId(1);
        try {
            $this->assertEquals(1, $this->productPrice->getFinalPrice(3, $product));
        } finally {
            $this->customerSession->setCustomerId(null);
        }
    }

    /**
     * Get price from custom object.
     *
     * @return void
     */
    public function testGetPrice(): void
    {
        $objectWithPrice = $this->objectManager->create(DataObject::class, ['data' => ['price' => 'test']]);
        $this->assertEquals('test', $this->productPrice->getPrice($objectWithPrice));
    }

    /**
     * Get product final price for different product count.
     *
     * @return void
     */
    public function testGetFinalPrice(): void
    {
        $product = $this->productRepository->get('simple');

        // regular & tier prices
        $this->assertEquals(10.0, $this->productPrice->getFinalPrice(1, $product));
        $this->assertEquals(8.0, $this->productPrice->getFinalPrice(2, $product));
        $this->assertEquals(5.0, $this->productPrice->getFinalPrice(5, $product));

        // with options
        $buyRequest = $this->prepareBuyRequest($product);
        $product->getTypeInstance()->prepareForCart($buyRequest, $product);

        //product price + options price(10+1+2+3+3)
        $this->assertEquals(19.0, $this->productPrice->getFinalPrice(1, $product));

        //product tier price + options price(5+1+2+3+3)
        $this->assertEquals(14.0, $this->productPrice->getFinalPrice(5, $product));
    }

    /**
     * Assert that formated price is correct.
     *
     * @return void
     */
    public function testGetFormatedPrice(): void
    {
        $product = $this->productRepository->get('simple');
        $this->assertEquals('<span class="price">$10.00</span>', $this->productPrice->getFormatedPrice($product));
    }

    /**
     * Test calculate price by date.
     *
     * @return void
     */
    public function testCalculatePrice(): void
    {
        $this->assertEquals(
            10,
            $this->productPrice->calculatePrice(10, 8, '1970-12-12 23:59:59', '1971-01-01 01:01:01')
        );
        $this->assertEquals(
            8,
            $this->productPrice->calculatePrice(10, 8, '1970-12-12 23:59:59', '2034-01-01 01:01:01')
        );
    }

    /**
     * Test calculate price by date.
     *
     * @return void
     */
    public function testCalculateSpecialPrice(): void
    {
        $this->assertEquals(
            10,
            $this->productPrice->calculateSpecialPrice(10, 8, '1970-12-12 23:59:59', '1971-01-01 01:01:01')
        );
        $this->assertEquals(
            8,
            $this->productPrice->calculateSpecialPrice(10, 8, '1970-12-12 23:59:59', '2034-01-01 01:01:01')
        );
    }

    /**
     * Assert that product tier price is fixed.
     *
     * @return void
     */
    public function testIsTierPriceFixed(): void
    {
        $this->assertTrue($this->productPrice->isTierPriceFixed());
    }

    /**
     * Build buy request based on product custom options.
     *
     * @param Product $product
     * @return DataObject
     */
    private function prepareBuyRequest(Product $product): DataObject
    {
        $options = [];
        /** @var Option $option */
        foreach ($product->getOptions() as $option) {
            switch ($option->getGroupByType()) {
                case ProductCustomOptionInterface::OPTION_GROUP_DATE:
                    $value = ['year' => 2013, 'month' => 8, 'day' => 9, 'hour' => 13, 'minute' => 35];
                    break;
                case ProductCustomOptionInterface::OPTION_GROUP_SELECT:
                    $value = key($option->getValues());
                    break;
                default:
                    $value = 'test';
                    break;
            }
            $options[$option->getId()] = $value;
        }

        return $this->objectManager->create(DataObject::class, ['data' => ['qty' => 1, 'options' => $options]]);
    }
}
