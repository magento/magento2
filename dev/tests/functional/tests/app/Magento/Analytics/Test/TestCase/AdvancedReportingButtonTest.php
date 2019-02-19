<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\TestCase;

use Magento\Mtf\TestCase\Injectable;
use Magento\Backend\Test\Page\Adminhtml\Dashboard;

/**
 * Steps:
 * 1. Log in to backend.
 * 2. Click on Advanced Reporting link.
 * 3. Perform asserts.
 *
 * @ZephyrId MAGETWO-63715
 */
class AdvancedReportingButtonTest extends Injectable
{
    /* tags */
    const MVP = 'no';
    /* end tags */

    /**
     * Run menu navigation test.
     *
     * @param Dashboard $dashboard
     * @return void
     */
    public function test(Dashboard $dashboard)
    {
        $dashboard->open();
        $dashboard->getReportsSectionBlock()->click();
    }
}
