<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Persistent\Test\TestCase;

use Magento\Customer\Test\Fixture\Customer;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Customer\Test\Page\CustomerAccountCreate;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Checkout\Test\Page\CheckoutCart;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Customer\Test\TestStep\LogoutCustomerOnFrontendStep;
use Magento\Mtf\TestCase\Injectable;
use Magento\Mtf\TestStep\TestStepFactory;

/**
 * Preconditions:
 * Apply configs:
 * 1. Enable Persistent Shopping Cart.
 * 2. Disable Clear Persistence on Sign Out.
 *
 * Steps:
 * 1. Go to frontend.
 * 2. Click Register link.
 * 3. Fill registry form.
 * 4. Click 'Create account' button.
 * 5. Add simple product to shopping cart.
 * 6. Sign out.
 *
 * @ZephyrId MAGETWO-45381
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CheckoutWithPersistentShoppingCartTest extends Injectable
{
    /**
     * Config data.
     *
     * @string $configData
     */
    private $configData;

    /**
     * Customer registry page.
     *
     * @var CustomerAccountCreate
     */
    private $customerAccountCreate;

    /**
     * Cms page.
     *
     * @var CmsIndex $cmsIndex.
     */
    private $cmsIndex;

    /**
     * Frontend product view page.
     *
     * @var CatalogProductView
     */
    private $catalogProductView;

    /**
     * Interface Browser.
     *
     * @var BrowserInterface.
     */
    private $browser;

    /**
     * Page of checkout page.
     *
     * @var CheckoutCart
     */
    private $checkoutCart;

    /**
     * Customer log out step.
     *
     * @var LogoutCustomerOnFrontendStep
     */
    private $logoutCustomerOnFrontendStep;

    /**
     * Factory for Test Steps.
     *
     * @var TestStepFactory
     */
    private $stepFactory;

    /**
     * Inject data.
     *
     * @param CustomerAccountCreate $customerAccountCreate
     * @param CmsIndex $cmsIndex
     * @param LogoutCustomerOnFrontendStep $logoutCustomerOnFrontendStep
     * @param CatalogProductView $catalogProductView
     * @param BrowserInterface $browser
     * @param CheckoutCart $checkoutCart
     * @param TestStepFactory $stepFactory
     * @return void
     */
    public function __inject(
        CustomerAccountCreate $customerAccountCreate,
        CmsIndex $cmsIndex,
        LogoutCustomerOnFrontendStep $logoutCustomerOnFrontendStep,
        CatalogProductView $catalogProductView,
        BrowserInterface $browser,
        CheckoutCart $checkoutCart,
        TestStepFactory $stepFactory
    ) {
        $this->customerAccountCreate = $customerAccountCreate;
        $this->cmsIndex = $cmsIndex;
        $this->logoutCustomerOnFrontendStep = $logoutCustomerOnFrontendStep;
        $this->browser = $browser;
        $this->catalogProductView = $catalogProductView;
        $this->checkoutCart = $checkoutCart;
        $this->stepFactory = $stepFactory;
    }

    /**
     * Prepare data.
     *
     * @param CatalogProductSimple $product
     * @return array
     */
    public function __prepare(CatalogProductSimple $product)
    {
        $product->persist();

        return ['product' => $product];
    }

    /**
     * Create Customer account on Storefront.
     *
     * @param string $configData
     * @param CatalogProductSimple $product
     * @param Customer $customer
     * @return void
     */
    public function test($configData, CatalogProductSimple $product, Customer $customer)
    {
        $this->configData = $configData;
        $this->stepFactory->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $configData]
        )->run();

        // Steps
        $this->cmsIndex->open();
        $this->cmsIndex->getLinksBlock()->openLink('Create an Account');
        $this->customerAccountCreate->getRegisterForm()->registerCustomer($customer);

        // Ensure that shopping cart is empty
        $this->checkoutCart->open()->getCartBlock()->clearShoppingCart();

        $this->browser->open($_ENV['app_frontend_url'] . $product->getUrlKey() . '.html');
        $this->catalogProductView->getViewBlock()->addToCart($product);
        $this->catalogProductView->getMessagesBlock()->waitSuccessMessage();
        $this->logoutCustomerOnFrontendStep->run();
    }

    /**
     * Clean data after running test.
     *
     * @return void
     */
    public function tearDown()
    {
        $this->stepFactory->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData, 'rollback' => true]
        )->run();
    }
}
