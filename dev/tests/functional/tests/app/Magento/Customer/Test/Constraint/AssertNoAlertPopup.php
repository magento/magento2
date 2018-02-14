<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Constraint;

use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Asserts that when try to Create an Order on the back-end
 * from the customer page, there is no pop-up
 */
class AssertNoAlertPopup extends AbstractConstraint
{
    /**
     * Assert no alert popup is displayed from Create Order page (Customer > select Customer > Create Order)
     *
     * @param BrowserInterface $browser
     * @return void
     */
    public function processAssert(BrowserInterface $browser)
    {
        $isAlertPresent = $this->isAlertPresent($browser);
        \PHPUnit_Framework_Assert::assertFalse($isAlertPresent, 'Alert pop up should not be visible.');
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Assert that no alert popup is displayed.';
    }

    /**
     * Check if alert is present.
     * 
     * @param BrowserInterface $browser
     * @return bool
     */
    private function isAlertPresent(BrowserInterface $browser)
    {
        $visible = false;
        try {
            $browser->getAlertText();
            $visible = true;
        } catch (\Exception $e) {
        }
        return $visible;
    }
}
