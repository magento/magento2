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

namespace Magento\Tax\Test\Constraint;

use Magento\Tax\Test\Fixture\TaxRate;
use Magento\Tax\Test\Page\Adminhtml\TaxRuleNew;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertTaxRateNotInTaxRule
 */
class AssertTaxRateNotInTaxRule extends AbstractConstraint
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'high';

    /**
     * Assert that tax rate is absent in tax rule form
     *
     * @param TaxRate $taxRate
     * @param TaxRuleNew $taxRuleNew
     * @return void
     */
    public function processAssert(
        TaxRate $taxRate,
        TaxRuleNew $taxRuleNew
    ) {
        $taxRuleNew->open();
        $taxRatesList = $taxRuleNew->getTaxRuleForm()->getAllTaxRates();
        \PHPUnit_Framework_Assert::assertFalse(
            in_array($taxRate->getCode(), $taxRatesList),
            'Tax Rate \'' . $taxRate->getCode() . '\' is present in Tax Rule form.'
        );
    }

    /**
     * Text of Tax Rate not in Tax Rule form
     *
     * @return string
     */
    public function toString()
    {
        return 'Tax rate is absent in tax rule from.';
    }
}
