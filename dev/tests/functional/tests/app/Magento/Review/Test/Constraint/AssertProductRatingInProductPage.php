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

namespace Magento\Review\Test\Constraint;

use Mtf\Client\Browser;
use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Review\Test\Fixture\ReviewInjectable;
use Magento\Review\Test\Fixture\Rating;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertProductRatingInProductPage
 * Assert that product rating is displayed on product review(frontend)
 */
class AssertProductRatingInProductPage extends AbstractConstraint
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'middle';

    /**
     * Assert that product rating is displayed on product review(frontend)
     *
     * @param CatalogProductView $catalogProductView
     * @param Browser $browser
     * @param CatalogProductSimple $product
     * @param ReviewInjectable|null $review [optional]
     * @param Rating|null $productRating [optional]
     * @return void
     */
    public function processAssert(
        CatalogProductView $catalogProductView,
        Browser $browser,
        CatalogProductSimple $product,
        ReviewInjectable $review = null,
        Rating $productRating = null
    ) {
        $browser->open($_ENV['app_frontend_url'] . $product->getUrlKey() . '.html');
        $reviewSummaryBlock = $catalogProductView->getReviewSummary();
        if ($reviewSummaryBlock->isVisible()) {
            $reviewSummaryBlock->getAddReviewLink()->click();
        }
        $rating = $productRating ? $productRating : $review->getDataFieldConfig('ratings')['source']->getRatings()[0];
        $reviewForm = $catalogProductView->getReviewFormBlock();
        \PHPUnit_Framework_Assert::assertTrue(
            $reviewForm->isVisibleRating($rating),
            'Product rating "' . $rating->getRatingCode() . '" is not displayed.'
        );
    }

    /**
     * Text success product rating is displayed
     *
     * @return string
     */
    public function toString()
    {
        return 'Product rating is displayed.';
    }
}
