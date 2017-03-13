<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CustomerImportExport\Test\TestCase;

use Magento\Mtf\TestCase\Scenario;

/**
 * Preconditions:
 * 1. Create customer.
 *
 * Steps:
 * 1. Login as admin.
 * 2. Open import index page.
 * 3. Fill import form.
 * 4. Click "Check Data" button.
 * 5. Perform assertions.
 *
 * @group ImportExport
 */
class ImportCustomerAddressTest extends Scenario
{
    /**
     * Run import data test.
     *
     * @return void
     */
    public function test()
    {
        $this->executeScenario();
    }
}
