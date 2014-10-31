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

namespace Magento\CheckoutAgreements\Test\Constraint;

use Magento\CheckoutAgreements\Test\Fixture\CheckoutAgreement;
use Magento\CheckoutAgreements\Test\Page\Adminhtml\CheckoutAgreementIndex;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertTermAbsentInGrid
 * Check that checkout agreement is absent in agreement grid.
 */
class AssertTermAbsentInGrid extends AbstractConstraint
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

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
