<?php
/**
 * Copyright © 2013-2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Analytics\Test\Page\Adminhtml\ConfigAnalytics;
use Magento\Backend\Test\Page\Adminhtml\Dashboard;
use Magento\Backend\Test\Page\Adminhtml\SystemConfigEdit;

/**
 * Assert Analytics is enabled in Stores > Configuration > General > Analytics > General menu.
 */
class AssertConfigAnalyticsEnabled extends AbstractConstraint
{
    /**
     * Assert Analytics is enabled in Stores > Configuration > General > Analytics menu.
     *
     * @param Dashboard $dashboard
     * @param SystemConfigEdit $systemConfigPage
     * @param ConfigAnalytics $configAnalytics
     */
    public function processAssert(
        Dashboard $dashboard,
        SystemConfigEdit $systemConfigPage,
        ConfigAnalytics $configAnalytics
    ) {
        $dashboard->open();
        $dashboard->getMenuBlock()->navigate('Stores > Configuration');
        $systemConfigPage->getForm()->getGroup('analytics', 'general');

        \PHPUnit_Framework_Assert::assertTrue(
            (bool)$configAnalytics->getAnalyticsForm()->isAnalyticsEnabled(),
            'Magento Analytics is disabled'
        );

        \PHPUnit_Framework_Assert::assertEquals(
            $configAnalytics->getAnalyticsForm()->getAnalyticsStatus(),
            'Subscription status: Pending',
            'Magento Analytics status is not pending'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Magento Analytics is enabled and has Pending status in
         Stores > Configuration > General > Analytics > General menu.';
    }
}
