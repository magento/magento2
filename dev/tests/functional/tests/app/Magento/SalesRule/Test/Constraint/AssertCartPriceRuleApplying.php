<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Test\Constraint;

use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Catalog\Test\Page\Category\CatalogCategoryView;
use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Checkout\Test\Page\CheckoutCart;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\Customer\Test\Fixture\Address;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Customer\Test\Page\CustomerAccountLogin;
use Magento\Customer\Test\Page\CustomerAccountLogout;
use Magento\SalesRule\Test\Fixture\SalesRule;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Abstract class for implementing assert applying.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class AssertCartPriceRuleApplying extends AbstractConstraint
{
    /**
     * Page CheckoutCart.
     *
     * @var CheckoutCart
     */
    protected $checkoutCart;

    /**
     * Page CmsIndex.
     *
     * @var CmsIndex
     */
    protected $cmsIndex;

    /**
     * Page CustomerAccountLogin.
     *
     * @var CustomerAccountLogin
     */
    protected $customerAccountLogin;

    /**
     * Page CustomerAccountLogout.
     *
     * @var CustomerAccountLogout
     */
    protected $customerAccountLogout;

    /**
     * Page CatalogCategoryView.
     *
     * @var CatalogCategoryView
     */
    protected $catalogCategoryView;

    /**
     * Page CatalogProductView.
     *
     * @var CatalogProductView
     */
    protected $catalogProductView;

    /**
     * Customer from precondition.
     *
     * @var Customer
     */
    protected $customer;

    /**
     * First product from precondition.
     *
     * @var CatalogProductSimple
     */
    protected $productForSalesRule1;

    /**
     * Second product from precondition.
     *
     * @var CatalogProductSimple
     */
    protected $productForSalesRule2;

    /**
     * Cart prices to compare.
     *
     * @array cartPrice
     */
    protected $cartPrice;

    /**
     * Implementation assert.
     *
     * @return void
     */
    abstract protected function assert();

    /**
     * 1. Navigate to frontend
     * 2. If "Log Out" link is visible and "isLoggedIn" empty
     *    - makes logout
     * 3. If "isLoggedIn" not empty
     *    - login as customer
     * 4. Clear shopping cart
     * 5. Add test product(s) to shopping cart with specify quantity
     * 6. If "salesRule/data/coupon_code" not empty:
     *    - fill "Enter your code" input in Dіscount Codes
     *    - click "Apply Coupon" button
     * 7. If "address/data/country_id" not empty:
     *    On Estimate Shipping and Tax:
     *    - fill Country, State/Province, Zip/Postal Code
     *    - click 'Get a Quote' button
     *    - select 'Flat Rate' shipping
     *    - click 'Update Total' button
     * 8. Implementation assert
     *
     * @param CheckoutCart $checkoutCart
     * @param CmsIndex $cmsIndex
     * @param CustomerAccountLogin $customerAccountLogin
     * @param CustomerAccountLogout $customerAccountLogout
     * @param CatalogCategoryView $catalogCategoryView
     * @param CatalogProductView $catalogProductView
     * @param SalesRule $salesRule
     * @param SalesRule $salesRuleOrigin
     * @param array $productQuantity
     * @param CatalogProductSimple $productForSalesRule1
     * @param CatalogProductSimple $productForSalesRule2
     * @param Customer $customer
     * @param Address $address
     * @param int|null $isLoggedIn
     * @param array $shipping
     * @param array $cartPrice
     * @return void
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function processAssert(
        CheckoutCart $checkoutCart,
        CmsIndex $cmsIndex,
        CustomerAccountLogin $customerAccountLogin,
        CustomerAccountLogout $customerAccountLogout,
        CatalogCategoryView $catalogCategoryView,
        CatalogProductView $catalogProductView,
        SalesRule $salesRule,
        SalesRule $salesRuleOrigin,
        array $productQuantity,
        CatalogProductSimple $productForSalesRule1,
        CatalogProductSimple $productForSalesRule2 = null,
        Customer $customer = null,
        Address $address = null,
        $isLoggedIn = null,
        array $shipping = [],
        array $cartPrice = []
    ) {
        $this->checkoutCart = $checkoutCart;
        $this->cmsIndex = $cmsIndex;
        $this->customerAccountLogin = $customerAccountLogin;
        $this->customerAccountLogout = $customerAccountLogout;
        $this->catalogCategoryView = $catalogCategoryView;
        $this->catalogProductView = $catalogProductView;
        $this->productForSalesRule1 = $productForSalesRule1;
        $this->productForSalesRule2 = $productForSalesRule2;
        $this->cartPrice = $cartPrice;
        if ($customer !== null) {
            $this->customer = $customer;
        }
        $isLoggedIn ? $this->login() : $this->customerAccountLogout->open();
        $this->checkoutCart->open()->getCartBlock()->clearShoppingCart();
        $this->addProductsToCart($productQuantity);
        $this->checkoutCart->open();
        if ($address !== null) {
            $this->checkoutCart->getShippingBlock()->fillEstimateShippingAndTax($address);
            $this->checkoutCart->getShippingBlock()->selectShippingMethod($shipping);
        }
        if ($salesRule->getCouponCode() || $salesRuleOrigin->getCouponCode()) {
            $this->checkoutCart->getDiscountCodesBlock()->applyCouponCode(
                $salesRule->getCouponCode() ? $salesRule->getCouponCode() : $salesRuleOrigin->getCouponCode()
            );
        }
        $this->assert();
    }

    /**
     * LogIn customer.
     *
     * @return void
     */
    protected function login()
    {
        $this->objectManager->create(
            'Magento\Customer\Test\TestStep\LoginCustomerOnFrontendStep',
            ['customer' => $this->customer]
        )->run();
    }

    /**
     * Add products to cart.
     *
     * @param array $productQuantity
     * @return void
     */
    protected function addProductsToCart(array $productQuantity)
    {
        foreach ($productQuantity as $product => $quantity) {
            if ($quantity > 0) {
                $categoryName = $this->$product->getCategoryIds()[0];
                $this->cmsIndex->getTopmenu()->selectCategoryByName($categoryName);
                $this->catalogCategoryView->getListProductBlock()->getProductItem($this->$product)->open();
                $this->catalogProductView->getViewBlock()->setQtyAndClickAddToCart($quantity);
                $this->catalogProductView->getMessagesBlock()->waitSuccessMessage();
            }
        }
    }
}
