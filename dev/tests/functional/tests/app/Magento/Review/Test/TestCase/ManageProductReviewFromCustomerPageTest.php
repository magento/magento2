<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Review\Test\TestCase;

use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndex;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndexEdit;
use Magento\Review\Test\Fixture\Review;
use Magento\Review\Test\Page\Adminhtml\RatingEdit;
use Magento\Review\Test\Page\Adminhtml\RatingIndex;
use Magento\Review\Test\Page\Adminhtml\ReviewEdit;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\TestCase\Injectable;

/**
 * Preconditions:
 * 1. Create Customer.
 * 2. Create simple product.
 * 3. Create Product review on the front.
 *
 * Steps:
 * 1. Open backend.
 * 2. Go to Customers -> All Customers.
 * 3. Open customer from preconditions.
 * 4. Open Product Review tab.
 * 5. Open Review created in preconditions.
 * 6. Fill data according to dataset.
 * 7. Click "Submit review".
 * 8. Perform all assertions.
 *
 * @group Reviews_and_Ratings_(MX)
 * @ZephyrId MAGETWO-27625
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ManageProductReviewFromCustomerPageTest extends Injectable
{
    /* tags */
    const MVP = 'no';
    const DOMAIN = 'MX';
    /* end tags */

    /**
     * Customer index page.
     *
     * @var CustomerIndex
     */
    protected $customerIndex;

    /**
     * Customer edit page.
     *
     * @var CustomerIndexEdit
     */
    protected $customerIndexEdit;

    /**
     * Catalog product view page.
     *
     * @var CatalogProductView
     */
    protected $catalogProductView;

    /**
     * Browser.
     *
     * @var BrowserInterface
     */
    protected $browser;

    /**
     * Backend rating grid page.
     *
     * @var RatingIndex
     */
    protected $ratingIndex;

    /**
     * Backend rating edit page.
     *
     * @var RatingEdit
     */
    protected $ratingEdit;

    /**
     * Review fixture.
     *
     * @var Review
     */
    protected $reviewInitial;

    /**
     * Review edit page.
     *
     * @var ReviewEdit
     */
    protected $reviewEdit;

    /**
     * Prepare data.
     *
     * @param Customer $customer
     * @return array
     */
    public function __prepare(Customer $customer)
    {
        $customer->persist();
        return ['customer' => $customer];
    }

    /**
     * Injection data.
     *
     * @param CustomerIndexEdit $customerIndexEdit
     * @param CustomerIndex $customerIndex
     * @param CatalogProductView $catalogProductView
     * @param BrowserInterface $browser
     * @param RatingIndex $ratingIndex
     * @param RatingEdit $ratingEdit
     * @param ReviewEdit $reviewEdit
     * @return void
     */
    public function __inject(
        CustomerIndexEdit $customerIndexEdit,
        CustomerIndex $customerIndex,
        CatalogProductView $catalogProductView,
        BrowserInterface $browser,
        RatingIndex $ratingIndex,
        RatingEdit $ratingEdit,
        ReviewEdit $reviewEdit
    ) {
        $this->customerIndexEdit = $customerIndexEdit;
        $this->customerIndex = $customerIndex;
        $this->catalogProductView = $catalogProductView;
        $this->browser = $browser;
        $this->ratingIndex = $ratingIndex;
        $this->ratingEdit = $ratingEdit;
        $this->reviewEdit = $reviewEdit;
    }

    /**
     * Run manage product review test.
     *
     * @param Review $reviewInitial
     * @param Review $review
     * @param Customer $customer
     * @return array
     */
    public function test(
        Review $reviewInitial,
        Review $review,
        Customer $customer
    ) {
        // Preconditions
        $this->login($customer);
        /** @var CatalogProductSimple $product */
        $product = $reviewInitial->getDataFieldConfig('entity_id')['source']->getEntity();
        $this->browser->open($_ENV['app_frontend_url'] . $product->getUrlKey() . '.html');
        $this->catalogProductView->getReviewSummary()->getAddReviewLink()->click();
        $this->catalogProductView->getReviewFormBlock()->fill($reviewInitial);
        $this->catalogProductView->getReviewFormBlock()->submit();
        $this->reviewInitial = $reviewInitial;
        // Steps
        $this->customerIndex->open();
        $this->customerIndex->getCustomerGridBlock()->searchAndOpen(['email' => $customer->getEmail()]);
        $this->customerIndexEdit->getCustomerForm()->openTab('product_reviews');
        $filter = [
            'title' => $reviewInitial->getTitle(),
            'sku' => $product->getSku(),
        ];
        $this->customerIndexEdit->getCustomerForm()->getTab('product_reviews')->getReviewsGrid()
            ->searchAndOpen($filter);
        $this->reviewEdit->getReviewForm()->fill($review);
        $this->reviewEdit->getPageActions()->save();

        return ['reviewInitial' => $reviewInitial, 'product' => $product];
    }

    /**
     * Login customer on frontend.
     *
     * @param Customer $customer
     * @return void
     */
    protected function login(Customer $customer)
    {
        $this->objectManager->create(
            'Magento\Customer\Test\TestStep\LoginCustomerOnFrontendStep',
            ['customer' => $customer]
        )->run();
    }

    /**
     * Clear data after test.
     *
     * @return void
     */
    public function tearDown()
    {
        $this->ratingIndex->open();
        if ($this->reviewInitial instanceof Review) {
            foreach ($this->reviewInitial->getRatings() as $rating) {
                $this->ratingIndex->getRatingGrid()->searchAndOpen(['rating_code' => $rating['title']]);
                $this->ratingEdit->getPageActions()->delete();
                $this->ratingEdit->getModalBlock()->acceptAlert();
            }
        }
    }
}
