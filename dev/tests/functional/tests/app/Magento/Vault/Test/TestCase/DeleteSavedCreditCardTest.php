<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Test\TestCase;

use Magento\Mtf\TestCase\Scenario;

/**
 * Preconditions:
 * 1. Credit card is saved during checkout
 *
 * Steps:
 * 1. Log in Storefront.
 * 2. Click 'My Account' link.
 * 3. Click 'My Credit Cards' tab.
 * 4. Click the 'Delete' button next to stored credit card.
 * 5. Click 'Delete' button.
 * 6. Go to One page Checkout
 * 7. Perform assertions.
 *
 * @group Vault_(CS)
 * @ZephyrId MAGETWO-48086
 */
class DeleteSavedCreditCardTest extends Scenario
{
    /* tags */
    const MVP = 'yes';
    const DOMAIN = 'CS';
    const TEST_TYPE = '3rd_party_test';
    /* end tags */

    /**
     * Runs delete saved credit card test.
     *
     * @return void
     */
    public function test()
    {
        $this->executeScenario();
    }
}
