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

use Magento\Review\Test\Page\Adminhtml\ReviewIndex;
use Magento\Review\Test\Page\Adminhtml\ReviewEdit;
use Magento\Review\Test\Fixture\ReviewInjectable;
use Mtf\Constraint\AbstractAssertForm;

/**
 * Class AssertProductRatingOnReviewPage
 */
class AssertProductRatingOnReviewPage extends AbstractAssertForm
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'middle';

    /**
     * Assert that product rating is displayed on product review(backend)
     *
     * @param ReviewIndex $reviewIndex
     * @param ReviewEdit $reviewEdit
     * @param ReviewInjectable $review
     * @param ReviewInjectable|null $reviewInitial [optional]
     * @return void
     */
    public function processAssert(
        ReviewIndex $reviewIndex,
        ReviewEdit $reviewEdit,
        ReviewInjectable $review,
        ReviewInjectable $reviewInitial = null
    ) {
        $filter = ['title' => $review->getTitle()];

        $reviewIndex->open();
        $reviewIndex->getReviewGrid()->searchAndOpen($filter);

        $ratingReview = array_replace(
            ($reviewInitial && $reviewInitial->hasData('ratings')) ? $reviewInitial->getRatings() : [],
            $review->hasData('ratings') ? $review->getRatings() : []
        );
        $ratingReview = $this->sortDataByPath($ratingReview, '::title');
        $ratingForm = $reviewEdit->getReviewForm()->getData();
        $ratingForm = $this->sortDataByPath($ratingForm['ratings'], '::title');
        $error = $this->verifyData($ratingReview, $ratingForm);
        \PHPUnit_Framework_Assert::assertTrue(empty($error), $error);
    }

    /**
     * Text success product rating is displayed on edit review page(backend)
     *
     * @return string
     */
    public function toString()
    {
        return 'Product rating is displayed on edit review page(backend).';
    }
}
