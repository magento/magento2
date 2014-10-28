<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Reports\Test\TestCase;

use Mtf\Client\Browser;
use Mtf\TestCase\Injectable;
use Mtf\Fixture\FixtureFactory;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\Review\Test\Fixture\ReviewInjectable;
use Magento\Customer\Test\Page\CustomerAccountLogin;
use Magento\Customer\Test\Fixture\CustomerInjectable;
use Magento\Customer\Test\Page\CustomerAccountLogout;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Catalog\Test\Page\Category\CatalogCategoryView;
use Magento\Reports\Test\Page\Adminhtml\ProductReportReview;

/**
 * Test Creation for CustomerReviewReportEntity
 *
 * Preconditions:
 * 1. Create customer
 * 2. Create simple product
 * 3. Open Product created in preconditions
 * 4. Click "Be the first to review this product "
 * 5. Fill data according to DataSet
 * 6. Click Submit review
 *
 * Test Flow:
 * 1. Open Reports -> Review : By Customers
 * 2. Assert Reviews qty
 * 3. Click Show Reviews
 * 4. Perform appropriate assertions.
 *
 * @group Reports_(MX)
 * @ZephyrId MAGETWO-27555
 */
class CustomerReviewReportEntityTest extends Injectable
{
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
     * Customer frontend login page
     *
     * @var CustomerAccountLogin
     */
    protected $customerAccountLogin;

    /**
     * Prepare data
     *
     * @param FixtureFactory $fixtureFactory
     * @return array
     */
    public function __prepare(FixtureFactory $fixtureFactory)
    {
        $customer = $fixtureFactory->createByCode('customerInjectable', ['dataSet' => 'johndoe_unique']);
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
     * @param CustomerAccountLogin $customerAccountLogin
     * @param CustomerAccountLogout $customerAccountLogout
     * @return void
     */
    public function __inject(
        ProductReportReview $productReportReview,
        CatalogProductView $pageCatalogProductView,
        CmsIndex $cmsIndex,
        CatalogCategoryView $catalogCategoryView,
        CustomerAccountLogin $customerAccountLogin,
        CustomerAccountLogout $customerAccountLogout
    ) {
        $this->productReportReview = $productReportReview;
        $this->pageCatalogProductView = $pageCatalogProductView;
        $this->cmsIndex = $cmsIndex;
        $this->catalogCategoryView = $catalogCategoryView;
        $this->customerAccountLogin = $customerAccountLogin;
        $this->customerAccountLogout = $customerAccountLogout;
    }

    /**
     * Test Creation for CustomerReviewReportEntity
     *
     * @param ReviewInjectable $review
     * @param CustomerInjectable $customer
     * @param $customerLogin
     * @param CatalogProductSimple $product
     * @param Browser $browser
     * @return array
     *
     * @SuppressWarnings(PHPMD.ConstructorWithNameAsEnclosingClass)
     */
    public function test(
        ReviewInjectable $review,
        CustomerInjectable $customer,
        CatalogProductSimple $product,
        Browser $browser,
        $customerLogin
    ) {
        // Preconditions
        $product->persist();
        $this->cmsIndex->open();
        if ($customerLogin == 'Yes') {
            $this->cmsIndex->getLinksBlock()->openLink("Log In");
            $this->customerAccountLogin->getLoginBlock()->login($customer);
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
