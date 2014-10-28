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

namespace Magento\Catalog\Test\Constraint;

use Mtf\Client\Browser;
use Mtf\Fixture\FixtureInterface;
use Mtf\Constraint\AbstractConstraint;
use Magento\Catalog\Test\Page\Product\CatalogProductView;

/**
 * Class AssertProductSpecialPriceOnProductPage
 */
class AssertProductSpecialPriceOnProductPage extends AbstractConstraint implements AssertPriceOnProductPageInterface
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

    /**
     * Error message
     *
     * @var string
     */
    protected $errorMessage = 'Assert that displayed special price on product page NOT equals to passed from fixture.';

    /**
     * Assert that displayed special price on product page equals passed from fixture
     *
     * @param CatalogProductView $catalogProductView
     * @param Browser $browser
     * @param FixtureInterface $product
     * @return void
     */
    public function processAssert(CatalogProductView $catalogProductView, Browser $browser, FixtureInterface $product)
    {
        $browser->open($_ENV['app_frontend_url'] . $product->getUrlKey() . '.html');

        //Process assertions
        $this->assertPrice($product, $catalogProductView);
    }

    /**
     * Set $errorMessage for special price assert
     *
     * @param string $errorMessage
     * @return void
     */
    public function setErrorMessage($errorMessage)
    {
        $this->errorMessage = $errorMessage;
    }

    /**
     * Verify product special price on product view page
     *
     * @param CatalogProductView $catalogProductView
     * @param FixtureInterface $product
     * @param string $block [optional]
     * @return void
     */
    public function assertPrice(
        FixtureInterface $product,
        CatalogProductView $catalogProductView,
        $block = ''
    ) {
        $fields = $product->getData();
        $specialPrice = $catalogProductView->getViewBlock()->getPriceBlock()->getSpecialPrice();
        if (isset($fields['special_price'])) {
            \PHPUnit_Framework_Assert::assertEquals(
                number_format($fields['special_price'], 2),
                $specialPrice,
                $this->errorMessage
            );
        }
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return "Assert that displayed special price on product page equals passed from fixture.";
    }
}
