<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ProductVideo\Test\Constraint;


use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Fixture\InjectableFixture;

/**
 * Assert that video is displayed on front end
 */
class AssertNoVideoProductView extends AbstractConstraint
{

    /**
     * Assert that video is not displayed on front end
     *
     * @param BrowserInterface $browser
     * @param InjectableFixture $product
     * @return void
     */
    public function processAssert(
        BrowserInterface $browser,
        InjectableFixture $product
    ) {
        //Open product view page
        $browser->open($_ENV['app_frontend_url'] . $product->getUrlKey() . '.html');
        $image = $browser->find('.fotorama__img');
        \PHPUnit_Framework_Assert::assertFalse(
            $image->isVisible(),
            'Product video is displayed on product view when it should not'
            );
    }


    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'No product video is displayed on product view.';
    }
}
