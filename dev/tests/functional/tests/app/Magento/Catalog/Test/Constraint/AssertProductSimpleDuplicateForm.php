<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Catalog\Test\Constraint;

/**
 * Class AssertProductSimpleDuplicateForm
 * Assert form data equals duplicate simple product data
 */
class AssertProductSimpleDuplicateForm extends AssertProductDuplicateForm
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';
}
