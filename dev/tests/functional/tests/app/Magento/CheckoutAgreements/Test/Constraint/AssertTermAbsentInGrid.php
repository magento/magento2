<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CheckoutAgreements\Test\Constraint;

use Magento\CheckoutAgreements\Test\Fixture\CheckoutAgreement;
use Magento\CheckoutAgreements\Test\Page\Adminhtml\CheckoutAgreementIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertTermAbsentInGrid
 * Check that checkout agreement is absent in agreement grid.
 */
class AssertTermAbsentInGrid extends AbstractConstraint
{
    /**
     * Assert that checkout agreement is absent in agreement grid.
     *
     * @param CheckoutAgreementIndex $agreementIndex
     * @param CheckoutAgreement $agreement
     * @return void
     */
    public function processAssert(CheckoutAgreementIndex $agreementIndex, CheckoutAgreement $agreement)
    {
        $agreementIndex->open();
        \PHPUnit_Framework_Assert::assertFalse(
            $agreementIndex->getAgreementGridBlock()->isRowVisible(['name' => $agreement->getName()]),
            'Checkout Agreement \'' . $agreement->getName() . '\' is present in agreement grid.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Checkout Agreement is absent in agreement grid.';
    }
}
