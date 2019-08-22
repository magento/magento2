<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExport\Test\TestCase;

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
 * @ZephyrId MAGETWO-47724, MAGETWO-47719, MAGETWO-47720
 */
class ImportProductsTest extends Scenario
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
