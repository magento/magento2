<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Swagger\Test\Constraint;

use Magento\Swagger\Test\Page\SwaggerUiPage;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertEndpointContentDisplay
 */
class AssertEndpointContentDisplay extends AbstractConstraint
{
    /**
     * Assert endpoint operation on swagger page
     *
     * @param SwaggerUiPage $swaggerPage
     * @param $serviceName
     * @param array $endpoints
     * @return void
     */
    public function processAssert(SwaggerUiPage $swaggerPage, $serviceName, array $endpoints)
    {
        foreach ($endpoints as $endpoint) {
            /**
             * Selector for operation content
             */
            $operationContentSelector = 'div[id$="%s%s_content"]';

            $operationContentSelector = sprintf($operationContentSelector, $serviceName, $endpoint);
            \PHPUnit_Framework_Assert::assertTrue(
                $swaggerPage->isElementVisible($operationContentSelector),
                'REST API endpoint operation content on swagger page.'
            );
        }
    }

    /**
     * Returns a string representation of successful assertion.
     *
     * @return string
     */
    public function toString()
    {
        return 'REST API endpoint operation content on swagger page.';
    }
}
