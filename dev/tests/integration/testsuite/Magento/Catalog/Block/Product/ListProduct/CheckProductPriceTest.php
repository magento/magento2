<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Block\Product\ListProduct;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Block\Product\ListProduct;
use Magento\Customer\Model\Session;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Result\PageFactory;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Check that product price render correctly on category page.
 *
 * @magentoDbIsolation enabled
 * @magentoAppArea frontend
 */
class CheckProductPriceTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var PageFactory
     */
    private $pageFactory;

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
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->pageFactory = $this->objectManager->get(PageFactory::class);
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $this->customerSession = $this->objectManager->create(Session::class);
        parent::setUp();
    }

    /**
     * Assert that product price without additional price configurations will render as expected.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple_tax_none.php
     *
     * @return void
     */
    public function testCheckProductPriceWithoutAdditionalPriceConfigurations(): void
    {
        $priceHtml = $this->getProductPriceHtml('simple-product-tax-none');
        $this->assertFinalPrice($priceHtml, 205.00);
    }

    /**
     * Assert that product special price rendered correctly.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_special_price.php
     *
     * @return void
     */
    public function testCheckSpecialPrice(): void
    {
        $priceHtml = $this->getProductPriceHtml('simple');
        $this->assertFinalPrice($priceHtml, 5.99);
        $this->assertRegularPrice($priceHtml, 10.00);
    }

    /**
     * Assert that product with fixed tier price is renders correctly.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple_with_fixed_tier_price.php
     *
     * @return void
     */
    public function testCheckFixedTierPrice(): void
    {
        $priceHtml = $this->getProductPriceHtml('simple-product-tax-none');
        $this->assertFinalPrice($priceHtml, 205.00);
        $this->assertAsLowAsPrice($priceHtml, 40.00);
    }

    /**
     * Assert that price of product with percent tier price rendered correctly.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple_with_percent_tier_price.php
     *
     * @return void
     */
    public function testCheckPercentTierPrice(): void
    {
        $priceHtml = $this->getProductPriceHtml('simple-product-tax-none');
        $this->assertFinalPrice($priceHtml, 205.00);
        $this->assertAsLowAsPrice($priceHtml, 102.50);
    }

    /**
     * Assert that price of product with fixed tier price for not logged user is renders correctly.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple_with_fixed_tier_price_for_not_logged_user.php
     *
     * @return void
     */
    public function testCheckFixedTierPriceForNotLoggedUser(): void
    {
        $priceHtml = $this->getProductPriceHtml('simple-product-tax-none');
        $this->assertFinalPrice($priceHtml, 30.00);
        $this->assertRegularPrice($priceHtml, 205.00);
    }

    /**
     * Assert that price of product with fixed tier price for logged user is renders correctly.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple_with_fixed_tier_price_for_logged_user.php
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDbIsolation disabled
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     *
     * @return void
     */
    public function testCheckFixedTierPriceForLoggedUser(): void
    {
        $priceHtml = $this->getProductPriceHtml('simple-product-tax-none');
        $this->assertFinalPrice($priceHtml, 205.00);
        $this->assertNotRegExp('/\$10/', $priceHtml);
        $this->customerSession->setCustomerId(1);
        try {
            $priceHtml = $this->getProductPriceHtml('simple-product-tax-none');
            $this->assertFinalPrice($priceHtml, 10.00);
            $this->assertRegularPrice($priceHtml, 205.00);
        } finally {
            $this->customerSession->setCustomerId(null);
        }
    }

    /**
     * Assert that price of product with catalog rule with action equal to "Apply as percentage of original"
     * is renders correctly.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple_tax_none.php
     * @magentoDataFixture Magento/CatalogRule/_files/rule_apply_as_percentage_of_original_not_logged_user.php
     * @magentoDbIsolation disabled
     * @magentoAppArea frontend
     *
     * @return void
     */
    public function testCheckPriceRendersCorrectlyWithApplyAsPercentageOfOriginalRule(): void
    {
        $priceHtml = $this->getProductPriceHtml('simple-product-tax-none');
        $this->assertFinalPrice($priceHtml, 184.50);
        $this->assertRegularPrice($priceHtml, 205.00);
    }

    /**
     * Assert that price of product with catalog rule with action equal to "Apply as fixed amount"
     * is renders correctly.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple_tax_none.php
     * @magentoDataFixture Magento/CatalogRule/_files/rule_apply_as_fixed_amount_not_logged_user.php
     * @magentoDbIsolation disabled
     * @magentoAppArea frontend
     *
     * @return void
     */
    public function testCheckPriceRendersCorrectlyWithApplyAsFixedAmountRule(): void
    {
        $priceHtml = $this->getProductPriceHtml('simple-product-tax-none');
        $this->assertFinalPrice($priceHtml, 195.00);
        $this->assertRegularPrice($priceHtml, 205.00);
    }

    /**
     * Assert that price of product with catalog rule with action equal to "Adjust final price to this percentage"
     * is renders correctly.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple_tax_none.php
     * @magentoDataFixture Magento/CatalogRule/_files/rule_adjust_final_price_to_this_percentage_not_logged_user.php
     * @magentoDbIsolation disabled
     * @magentoAppArea frontend
     *
     * @return void
     */
    public function testCheckPriceRendersCorrectlyWithAdjustFinalPriceToThisPercentageRule(): void
    {
        $priceHtml = $this->getProductPriceHtml('simple-product-tax-none');
        $this->assertFinalPrice($priceHtml, 20.50);
        $this->assertRegularPrice($priceHtml, 205.00);
    }

    /**
     * Assert that price of product with catalog rule with action equal to "Adjust final price to discount value"
     * is renders correctly.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple_tax_none.php
     * @magentoDataFixture Magento/CatalogRule/_files/rule_adjust_final_price_to_discount_value_not_logged_user.php
     * @magentoDbIsolation disabled
     * @magentoAppArea frontend
     *
     * @return void
     */
    public function testCheckPriceRendersCorrectlyWithAdjustFinalPriceToDiscountValueRule(): void
    {
        $priceHtml = $this->getProductPriceHtml('simple-product-tax-none');
        $this->assertFinalPrice($priceHtml, 10.00);
        $this->assertRegularPrice($priceHtml, 205.00);
    }

    /**
     * Assert that price html contain "As low as" label and expected price amount.
     *
     * @param string $priceHtml
     * @param float $expectedPrice
     * @return void
     */
    private function assertAsLowAsPrice(string $priceHtml, float $expectedPrice): void
    {
        $this->assertRegExp(
            sprintf(
                '/<span class="price-label">As low as<\/span> {1,}<span.*data-price-amount="%s".*>\$%01.2f<\/span>/',
                round($expectedPrice, 2),
                $expectedPrice
            ),
            $priceHtml
        );
    }

    /**
     * Assert that price html contain expected final price amount.
     *
     * @param string $priceHtml
     * @param float $expectedPrice
     * @return void
     */
    private function assertFinalPrice(string $priceHtml, float $expectedPrice): void
    {
        $this->assertRegExp(
            sprintf(
                '/data-price-type="finalPrice".*<span class="price">\$%01.2f<\/span><\/span>/',
                $expectedPrice
            ),
            $priceHtml
        );
    }

    /**
     * Assert that price html contain "Regular price" label and expected price amount.
     *
     * @param string $priceHtml
     * @param float $expectedPrice
     * @return void
     */
    private function assertRegularPrice(string $priceHtml, float $expectedPrice): void
    {
        $regex = '<span class="price-label">Regular Price<\/span> {1,}<span.*data-price-amount="%s".*>\$%01.2f<\/span>';
        $this->assertRegExp(
            sprintf("/{$regex}/", round($expectedPrice, 2), $expectedPrice),
            $priceHtml
        );
    }

    /**
     * Return html of product price without new line characters.
     *
     * @param string $sku
     * @return string
     */
    private function getProductPriceHtml(string $sku): string
    {
        $product = $this->productRepository->get($sku, false, null, true);

        return preg_replace('/[\n\r]/', '', $this->getListProductBlock()->getProductPrice($product));
    }

    /**
     * Get list product block from layout.
     *
     * @return ListProduct
     */
    private function getListProductBlock(): ListProduct
    {
        $page = $this->pageFactory->create();
        $page->addHandle([
            'default',
            'catalog_category_view',
        ]);
        $page->getLayout()->generateXml();
        /** @var Template $categoryProductsBlock */
        $categoryProductsBlock = $page->getLayout()->getBlock('category.products');

        return $categoryProductsBlock->getChildBlock('product_list');
    }
}
