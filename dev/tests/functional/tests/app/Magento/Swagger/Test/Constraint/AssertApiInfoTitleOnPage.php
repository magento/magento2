<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Swagger\Test\Constraint;

use Magento\Swagger\Test\Page\SwaggerUiPage;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertApiInfoTitleOnPage
 */
class AssertApiInfoTitleOnPage extends AbstractConstraint
{
    /**
     * Selector for API info title
     *
     * @var string
     */
    protected $titleSelector = '.info_title';

    /**
     * Assert API info title on swagger page
     *
     * @param SwaggerUiPage $swaggerPage
     * @return void
     */
    public function processAssert(SwaggerUiPage $swaggerPage)
    {
        \PHPUnit_Framework_Assert::assertTrue(
            $swaggerPage->isElementVisible($this->titleSelector),
            'REST API info title on swagger page.'
        );
    }

    /**
     * Returns a string representation of successful assertion.
     *
     * @return string
     */
    public function toString()
    {
        return 'REST API info title on swagger page.';
    }
}
