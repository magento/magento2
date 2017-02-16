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
 * Assert sending data to the Analytics is restored.
 */
class AssertConfigAnalyticsRestored extends AbstractConstraint
{
    /**
     * Assert sending data to the Analytics is restored.
     *
     * @param ConfigAnalytics $configAnalytics
     * @param Dashboard $dashboard
     * @param SystemConfigEdit $systemConfigPage
     * @param string $vertical
     * @return void
     */
    public function processAssert(
        ConfigAnalytics $configAnalytics,
        Dashboard $dashboard,
        SystemConfigEdit $systemConfigPage,
        $vertical
    ) {
        $this->objectManager->create(
            \Magento\Analytics\Test\TestStep\OpenAnalyticsConfigStep::class,
            [
                'dashboard' => $dashboard,
                'systemConfigPage' => $systemConfigPage
            ]
        )->run();

        $configAnalytics->getAnalyticsForm()->enableAnalytics();
        $configAnalytics->getAnalyticsForm()->setAnalyticsVertical($vertical);
        $configAnalytics->getAnalyticsForm()->saveConfig();

        \PHPUnit_Framework_Assert::assertTrue(
            $systemConfigPage->getMessagesBlock()->assertSuccessMessage(),
            'Sending data to the Analytics is not saved'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Sending data to the Analytics is saved';
    }
}
