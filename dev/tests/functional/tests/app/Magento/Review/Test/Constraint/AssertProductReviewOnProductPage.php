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
use Mtf\Fixture\FixtureInterface;
use Mtf\Constraint\AbstractConstraint;
use Magento\Review\Test\Fixture\ReviewInjectable;
use Magento\Backend\Test\Page\Adminhtml\AdminCache;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Catalog\Test\Page\Product\CatalogProductView;

/**
 * Class AssertProductReviewOnProductPage
 * Assert that product review available on product page
 */
class AssertProductReviewOnProductPage extends AbstractConstraint
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'middle';

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
