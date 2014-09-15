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

namespace Magento\Review\Test\TestCase;

use Mtf\Client\Browser;
use Mtf\TestCase\Injectable;
use Magento\Review\Test\Fixture\ReviewInjectable;
use Magento\Review\Test\Page\Adminhtml\RatingEdit;
use Magento\Review\Test\Page\Adminhtml\RatingIndex;
use Magento\Catalog\Test\Page\Product\CatalogProductView;

/**
 * Test Creation for Create Frontend Product Review
 *
 * Test Flow:
 *
 * Preconditions:
 * 1. Create simple product
 * 2. Create custom rating type
 *
 * Steps:
 * 1. Open frontend
 * 2. Go to product page
 * 3. Click "Be the first to review this product"
 * 4. Fill data according to dataset
 * 5. click "Submit review"
 * 6. Perform all assertions
 *
 * @group Reviews_and_Ratings_(MX)
 * @ZephyrId MAGETWO-25519
 */
class CreateProductReviewFrontendEntityTest extends Injectable
{
    /**
     * Frontend product view page
     *
     * @var CatalogProductView
     */
    protected $catalogProductView;

    /**
     * Backend rating grid page
     *
     * @var RatingIndex
     */
    protected $ratingIndex;

    /**
     * Backend rating edit page
     *
     * @var RatingEdit
     */
    protected $ratingEdit;

    /**
     * Fixture review
     *
     * @var ReviewInjectable
     */
    protected $review;

    /**
     * Injection data
     *
     * @param CatalogProductView $catalogProductView
     * @param RatingIndex $ratingIndex
     * @param RatingEdit $ratingEdit
     * @return void
     */
    public function __inject(
        CatalogProductView $catalogProductView,
        RatingIndex $ratingIndex,
        RatingEdit $ratingEdit
    ) {
        $this->catalogProductView = $catalogProductView;
        $this->ratingIndex = $ratingIndex;
        $this->ratingEdit = $ratingEdit;
    }

    /**
     * Run create frontend product rating test
     *
     * @param ReviewInjectable $review
     * @param Browser $browser
     * @return array
     */
    public function test(ReviewInjectable $review, Browser $browser)
    {
        // Prepare for tear down
        $this->review = $review;

        // Steps
        $product = $review->getDataFieldConfig('entity_id')['source']->getEntity();
        $browser->open($_ENV['app_frontend_url'] . $product->getUrlKey() . '.html');
        $reviewLink = $this->catalogProductView->getReviewSummary()->getAddReviewLink();
        if ($reviewLink->isVisible()) {
            $reviewLink->click();
        }
        $reviewForm = $this->catalogProductView->getReviewFormBlock();
        $reviewForm->fill($review);
        $reviewForm->submit();

        return ['product' => $product];
    }

    /**
     * Clear data after test
     *
     * @return void
     */
    public function tearDown()
    {
        $this->ratingIndex->open();
        if ($this->review instanceof ReviewInjectable) {
            foreach ($this->review->getRatings() as $rating) {
                $this->ratingIndex->getRatingGrid()->searchAndOpen(['rating_code' => $rating['title']]);
                $this->ratingEdit->getPageActions()->delete();
            }
        }
    }
}
