<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Test\TestCase;

use Magento\Mtf\TestCase\Scenario;

/**
 * Preconditions:
 * 1. Create products.
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
class ImportDataNegativeTest extends Scenario
{
    /* tags */
    const SEVERITY = 'S1';
    /* end tags */

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
