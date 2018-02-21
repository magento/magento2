<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Test\TestCase;

use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Catalog\Test\Page\Category\CatalogCategoryView;
use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Customer\Test\Page\CustomerAccountLogout;
use Magento\Reports\Test\Page\Adminhtml\ProductReportReview;
use Magento\Review\Test\Fixture\Review;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestCase\Injectable;

/**
 * Preconditions:
 * 1. Create customer
 * 2. Create simple product
 * 3. Open Product created in preconditions
 * 4. Click "Be the first to review this product "
 * 5. Fill data according to DataSet
 * 6. Click Submit review
 *
 * Steps:
 * 1. Open Reports -> Review : By Customers
 * 2. Assert Reviews qty
 * 3. Click Show Reviews
 * 4. Perform appropriate assertions.
 *
 * @group Reports_(MX)
 * @ZephyrId MAGETWO-27555
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CustomerReviewReportEntityTest extends Injectable
{
    /* tags */
    const MVP = 'no';
    const DOMAIN = 'MX';
    /* end tags */

    /**
     * Customer frontend logout page
     *
     * @var CustomerAccountLogout
     */
    protected $customerAccountLogout;

    /**
     * Product reviews report page
     *
     * @var ProductReportReview
     */
    protected $productReportReview;

    /**
     * Frontend product view page
     *
     * @var CatalogProductView
     */
    protected $pageCatalogProductView;

    /**
     * Cms Index page
     *
     * @var CmsIndex
     */
    protected $cmsIndex;

    /**
     * Catalog Category page
     *
     * @var CatalogCategoryView
     */
    protected $catalogCategoryView;

    /**
     * Prepare data
     *
     * @param FixtureFactory $fixtureFactory
     * @return array
     */
    public function __prepare(FixtureFactory $fixtureFactory)
    {
        $customer = $fixtureFactory->createByCode('customer', ['dataset' => 'johndoe_unique']);
        $customer->persist();

        return ['customer' => $customer];
    }

    /**
     * Preparing pages for test
     *
     * @param ProductReportReview $productReportReview
     * @param CatalogProductView $pageCatalogProductView
     * @param CmsIndex $cmsIndex
     * @param CatalogCategoryView $catalogCategoryView
     * @param CustomerAccountLogout $customerAccountLogout
     * @return void
     */
    public function __inject(
        ProductReportReview $productReportReview,
        CatalogProductView $pageCatalogProductView,
        CmsIndex $cmsIndex,
        CatalogCategoryView $catalogCategoryView,
        CustomerAccountLogout $customerAccountLogout
    ) {
        $this->productReportReview = $productReportReview;
        $this->pageCatalogProductView = $pageCatalogProductView;
        $this->cmsIndex = $cmsIndex;
        $this->catalogCategoryView = $catalogCategoryView;
        $this->customerAccountLogout = $customerAccountLogout;
    }

    /**
     * Test Creation for CustomerReviewReportEntity
     *
     * @param Review $review
     * @param Customer $customer
     * @param $customerLogin
     * @param CatalogProductSimple $product
     * @param BrowserInterface $browser
     * @return array
     *
     * @SuppressWarnings(PHPMD.ConstructorWithNameAsEnclosingClass)
     */
    public function test(
        Review $review,
        Customer $customer,
        CatalogProductSimple $product,
        BrowserInterface $browser,
        $customerLogin
    ) {
        // Preconditions
        $product->persist();
        $this->cmsIndex->open();
        if ($customerLogin == 'Yes') {
            $this->objectManager->create(
                'Magento\Customer\Test\TestStep\LoginCustomerOnFrontendStep',
                ['customer' => $customer]
            )->run();
        }
        // Steps
        $browser->open($_ENV['app_frontend_url'] . $product->getUrlKey() . '.html');
        $this->pageCatalogProductView->getReviewSummary()->getAddReviewLink()->click();
        $this->pageCatalogProductView->getReviewFormBlock()->fill($review);
        $this->pageCatalogProductView->getReviewFormBlock()->submit();

        return ['product' => $product];
    }

    /**
     * Logout customer from frontend account
     *
     * return void
     */
    public function tearDown()
    {
        $this->customerAccountLogout->open();
    }
}
