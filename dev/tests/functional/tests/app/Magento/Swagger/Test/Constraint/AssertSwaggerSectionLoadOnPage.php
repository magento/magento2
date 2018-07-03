<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Swagger\Test\Constraint;

use Magento\Swagger\Test\Page\SwaggerUiPage;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertSwaggerSectionLoadOnPage
 */
class AssertSwaggerSectionLoadOnPage extends AbstractConstraint
{
    /**
     * Selector for class 'swagger-section'
     *
     * @var string
     */
    protected $swaggerSectionSelector = '.swagger-section';

    /**
     * Assert class swagger-section on swagger page
     *
     * @param SwaggerUiPage $swaggerPage
     * @return void
     */
    public function processAssert(SwaggerUiPage $swaggerPage)
    {
        \PHPUnit\Framework\Assert::assertTrue(
            $swaggerPage->isElementVisible($this->swaggerSectionSelector),
            'Class swagger-section on swagger page.'
        );
    }

    /**
     * Returns a string representation of successful assertion.
     *
     * @return string
     */
    public function toString()
    {
        return 'Class swagger-section on swagger page.';
    }
}
