<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Test\Constraint;

use Magento\Tax\Test\Page\Adminhtml\TaxConfiguration;
use Magento\Tax\Test\Page\Adminhtml\TaxRuleIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Check notification message after save tax configuration appear in global message block.
 */
class AssertTaxConfigurationNotificationMessage extends AbstractConstraint
{
    // @codingStandardsIgnoreStart
    const NOTIFICATION = 'To apply the discount on prices including tax and apply the tax after discount, set Catalog Prices to “Including Tax”.';
    // @codingStandardsIgnoreEnd

    /**
     * Assert notification is displayed after tax configuration saved.
     *
     * @param TaxConfiguration $taxConfiguration
     * @return void
     */
    public function processAssert(TaxConfiguration $taxConfiguration)
    {
        if (!$taxConfiguration->getNotificationBlockPopup()->isVisible()) {
            $taxConfiguration->getNotificationBlock()->openNotificationPopup();
        }
        $message = $taxConfiguration->getNotificationBlockPopup()->getNotificationMessage();

        \PHPUnit_Framework_Assert::assertContains(
            self::NOTIFICATION,
            $message,
            "Notification wasn't displayed."
        );
    }

    /**
     * Text of Saved Tax Configuration Notification Message assert.
     *
     * @return string
     */
    public function toString()
    {
        return 'Tax notification message is present.';
    }
}
