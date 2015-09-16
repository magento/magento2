<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ProductVideo\Test\Constraint;


use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Fixture\InjectableFixture;

/**
 * Assert that video is displayed on front end
 */
class AssertNoVideoCategoryView extends AbstractConstraint
{

    /**
     * Assert that video is not displayed on front end
     *
     * @param CmsIndex $cmsIndex
     * @param BrowserInterface $browser
     * @param InjectableFixture $product
     * @return void
     */
    public function processAssert(
        CmsIndex $cmsIndex,
        BrowserInterface $browser,
        InjectableFixture $product
    ) {
        $cmsIndex->open();
        $cmsIndex->getTopmenu()->selectCategoryByName($product->getCategoryIds()[0]);
        $photo = $browser->find('.product-image-photo');
        $src = $photo->getAttribute('src');
        \PHPUnit_Framework_Assert::assertTrue(
            strpos($src, '/placeholder/') !== false,
            'Product image is displayed on category view when it should not'
        );
    }


    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'No product images is displayed on category view.';
    }
}
