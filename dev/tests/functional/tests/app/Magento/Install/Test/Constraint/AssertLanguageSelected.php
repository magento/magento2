<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Install\Test\Constraint;

use Magento\Cms\Test\Page\CmsIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that selected language currently displays on frontend.
 */
class AssertLanguageSelected extends AbstractConstraint
{
    /**
     * Assert that selected language currently displays on frontend.
     *
     * @param string $languageTemplate
     * @param CmsIndex $indexPage
     * @return void
     */
    public function processAssert($languageTemplate, CmsIndex $indexPage)
    {
        $indexPage->open();
        \PHPUnit_Framework_Assert::assertTrue(
            $indexPage->getFooterBlock()->isLinkVisible($languageTemplate),
            'Selected language not displays on frontend.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Selected language currently displays on frontend.';
    }
}
