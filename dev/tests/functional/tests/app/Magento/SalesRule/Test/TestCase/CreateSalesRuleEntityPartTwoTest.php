<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Test\TestCase;

use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestCase\Injectable;
use Magento\SalesRule\Test\Block\Adminhtml\Promo\Quote\Edit\Section\ManageCouponCode;
use Magento\SalesRule\Test\Fixture\SalesRule;
use Magento\SalesRule\Test\Page\Adminhtml\PromoQuoteEdit;
use Magento\SalesRule\Test\Page\Adminhtml\PromoQuoteIndex;
use Magento\SalesRule\Test\Page\Adminhtml\PromoQuoteNew;

/**
 * Precondition:
 * 1. 2 sub categories in Default Category are created.
 * 2. 2 simple products are created and assigned to different subcategories by one for each.
 * 3. Default customer are created.
 *
 * Steps:
 * 1. Login to backend as admin.
 * 2. Navigate to MARKETING > Cart Price Rule.
 * 3. Create Cart Price rule according to dataset and click "Save" button.
 * 4. Perform asserts.
 *
 * @group Shopping_Cart_Price_Rules
 * @ZephyrId MAGETWO-24855
 */
class CreateSalesRuleEntityPartTwoTest extends CreateSalesRuleEntityTest
{
    /* tags */
    const MVP = 'yes';
    const TEST_TYPE = 'extended_acceptance_test';
    /* end tags */

    // This blank class is created only to run long variation(s) as a separate test in parallel environment
}
