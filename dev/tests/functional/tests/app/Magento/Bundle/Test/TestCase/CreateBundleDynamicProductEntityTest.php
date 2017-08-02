<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Test\TestCase;

/**
 * Test Flow:
 * 1. Login as admin
 * 2. Navigate to the Products>Inventory>Catalog
 * 3. Click on "+" dropdown and select Bundle Product type
 * 4. Fill in all data according to data set
 * 5. Save product
 * 6. Verify created product
 *
 * @group Bundle_Product
 * @ZephyrId MAGETWO-24118
 */
class CreateBundleDynamicProductEntityTest extends CreateBundleProductEntityTest
{
    /* tags */
    const TEST_TYPE = 'acceptance_test, extended_acceptance_test';
    const MVP = 'yes';
    /* end tags */

    // This blank class is created only to run long variation as a separate test in parallel environment
}
