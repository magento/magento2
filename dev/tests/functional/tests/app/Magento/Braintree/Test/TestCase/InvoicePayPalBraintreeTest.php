<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Braintree\Test\TestCase;

use Magento\Mtf\TestCase\Scenario;

/**
 * Preconditions:
 * 1. Order 1 is placed with Braintree PayPal.
 *
 * Steps:
 * 1. Log in to Admin.
 * 2. Go to Sales > Orders page.
 * 3. Open order 1.
 * 4. Click Invoice button.
 * 5. Ensure Capture Online is selected, click Submit Invoice button.
 * 6. Open Invoices tab.
 * 7. Perform assertions.
 *
 * @group Braintree
 * @ZephyrId MAGETWO-48614, MAGETWO-48615
 */
class InvoicePayPalBraintreeTest extends Scenario
{
    /* tags */
    const MVP = 'yes';
    const TEST_TYPE = '3rd_party_test';
    const SEVERITY = 'S1';
    /* end tags */

    /**
     * Runs one page checkout test.
     *
     * @return void
     */
    public function test()
    {
        $this->executeScenario();
    }
}
