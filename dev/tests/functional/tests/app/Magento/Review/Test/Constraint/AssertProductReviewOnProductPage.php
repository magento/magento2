<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Review\Test\Constraint;

use Magento\PageCache\Test\Page\Adminhtml\AdminCache;
use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Review\Test\Fixture\Review;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Assert that product review available on product page.
 */
class AssertProductReviewOnProductPage extends AbstractConstraint
{
    /**
     * Assert that product review available on product page.
     *
     * @param CatalogProductView $catalogProductView
     * @param Review $review
     * @param FixtureInterface $product
     * @param BrowserInterface $browser
     * @param AdminCache $cachePage
     * @return void
     */
    public function processAssert(
        CatalogProductView $catalogProductView,
        Review $review,
        FixtureInterface $product,
        BrowserInterface $browser,
        AdminCache $cachePage
    ) {
        $errors = [];
        $cachePage->open()->getActionsBlock()->flushMagentoCache();
        $browser->open($_ENV['app_frontend_url'] . $product->getUrlKey() . '.html');

        $reviewBlock = $catalogProductView->getCustomerReviewBlock();
        $catalogProductView->getViewBlock()->selectTab('Reviews');

        \PHPUnit\Framework\Assert::assertContains(
            sprintf("You're reviewing:\n%s", $product->getName()),
            $catalogProductView->getReviewFormBlock()->getLegend()->getText()
        );

        foreach ($review->getData() as $name => $value) {
            $reviewValue = $reviewBlock->getFieldValue($name);
            if (($reviewValue !== null) && (0 !== strcasecmp($value, trim($reviewValue)))) {
                $errors[] = '- field "' . $name . '" is not equals submitted one, error value "' . $value . '"';
            }
        }

        \PHPUnit\Framework\Assert::assertEmpty(
            $errors,
            'The Review contains the following errors:' . PHP_EOL . implode(PHP_EOL, $errors)
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Product review is displayed correct.';
    }
}
