<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Tax\Test\Constraint;

use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertTaxRateInTaxRule
 */
class AssertTaxRateInTaxRule extends AbstractConstraint
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

    /**
     * @return void
     */
    public function processAssert()
    {
        //
    }

    /**
     * @return string
     */
    public function toString()
    {
        //
    }
}
