<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Review\Test\Constraint;

use Magento\Backend\Test\Page\Adminhtml\AdminCache;
use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Review\Test\Fixture\ReviewInjectable;
use Mtf\Client\Browser;
use Mtf\Constraint\AbstractConstraint;
use Mtf\Fixture\FixtureInterface;

/**
 * Class AssertProductReviewOnProductPage
 * Assert that product review available on product page
 */
class AssertProductReviewOnProductPage extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'middle';
    /* end tags */

    /**
     * Assert that product review available on product page
     *
     * @param CatalogProductView $catalogProductView
     * @param ReviewInjectable $review
     * @param FixtureInterface $product
     * @param Browser $browser
     * @param AdminCache $cachePage
     * @return void
     */
    public function processAssert(
        CatalogProductView $catalogProductView,
        ReviewInjectable $review,
        FixtureInterface $product,
        Browser $browser,
        AdminCache $cachePage
    ) {
        $errors = [];
        $cachePage->open()->getActionsBlock()->flushMagentoCache();
        $browser->open($_ENV['app_frontend_url'] . $product->getUrlKey() . '.html');

        $reviewBlock = $catalogProductView->getCustomerReviewBlock();
        $catalogProductView->getViewBlock()->selectTab('Reviews');
        foreach ($review->getData() as $name => $value) {
            $reviewValue = $reviewBlock->getFieldValue($name);
            if (($reviewValue !== null) && (0 !== strcasecmp($value, trim($reviewValue)))) {
                $errors[] = '- field "' . $name . '" is not equals submitted one, error value "' . $value . '"';
            }
        }

        \PHPUnit_Framework_Assert::assertEmpty(
            $errors,
            'The Review contains the following errors:' . PHP_EOL . implode(PHP_EOL, $errors)
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Product review is displayed correct.';
    }
}
