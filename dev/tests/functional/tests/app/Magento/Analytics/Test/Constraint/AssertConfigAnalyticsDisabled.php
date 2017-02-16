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
 * Assert Analytics is disabled in Stores>Configuration>General>Analytics->General menu.
 */
class AssertConfigAnalyticsDisabled extends AbstractConstraint
{
    /**
     * Assert Analytics is disabled in Stores > Configuration > General > Analytics menu.
     *
     * @param Dashboard $dashboard
     * @param SystemConfigEdit $systemConfigPage
     * @param ConfigAnalytics $configAnalytics
     * @return void
     */
    public function processAssert(
        Dashboard $dashboard,
        SystemConfigEdit $systemConfigPage,
        ConfigAnalytics $configAnalytics
    ) {
        $dashboard->open();
        $dashboard->getMenuBlock()->navigate('Stores > Configuration');
        $systemConfigPage->getForm()->getGroup('analytics', 'general');

        \PHPUnit_Framework_Assert::assertFalse(
            (bool)$configAnalytics->getAnalyticsForm()->isAnalyticsEnabled(),
            'Magento Analytics is not disabled'
        );

        \PHPUnit_Framework_Assert::assertEquals(
            $configAnalytics->getAnalyticsForm()->getAnalyticsStatus(),
            'Subscription status: Disabled',
            'Magento Analytics status is not disabled'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Magento Analytics is disabled in Stores > Configuration > General > Analytics > General menu.';
    }
}
