<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Tax\Test\Page\Adminhtml\TaxConfiguration;
use Magento\Tax\Test\Page\Adminhtml\TaxRuleIndex;

/**
 * Check success message after save tax configuration.
 */
class AssertTaxConfigurationSuccessSaveMessage extends AbstractConstraint
{
    const SUCCESS_MESSAGE = 'You saved the configuration.';

    /**
     * Assert that success message is displayed after tax configuration saved.
     *
     * @param TaxConfiguration $taxConfiguration
     * @return void
     */
    public function processAssert(TaxConfiguration $taxConfiguration)
    {
        $actualMessage = $taxConfiguration->getMessagesBlock()->getSuccessMessage();
        \PHPUnit_Framework_Assert::assertEquals(
            self::SUCCESS_MESSAGE,
            $actualMessage,
            "Tax configuration was not saved."
        );
    }

    /**
     * Text of Saved Tax Configuration Success Message assert.
     *
     * @return string
     */
    public function toString()
    {
        return 'Save tax configuration success message is present.';
    }
}
