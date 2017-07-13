<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Constraint;

use Magento\Analytics\Test\Page\Adminhtml\ConfigAnalytics;
use Magento\Analytics\Test\TestStep\OpenAnalyticsConfigStep;
use Magento\Backend\Test\Page\Adminhtml\SystemConfigEdit;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert sending data to the Analytics is restored.
 */
class AssertConfigAnalyticsRestored extends AbstractConstraint
{
    /**
     * Assert sending data to the Analytics is restored.
     *
     * @param ConfigAnalytics $configAnalytics
     * @param OpenAnalyticsConfigStep $openAnalyticsConfigStep
     * @param SystemConfigEdit $systemConfigPage
     * @param string $vertical
     * @return void
     */
    public function processAssert(
        ConfigAnalytics $configAnalytics,
        OpenAnalyticsConfigStep $openAnalyticsConfigStep,
        SystemConfigEdit $systemConfigPage,
        $vertical
    ) {
        $openAnalyticsConfigStep->run();

        $configAnalytics->getAnalyticsForm()->analyticsToggle();
        $configAnalytics->getAnalyticsForm()->setAnalyticsVertical($vertical);
        $configAnalytics->getAnalyticsForm()->saveConfig();

        \PHPUnit_Framework_Assert::assertTrue(
            $systemConfigPage->getMessagesBlock()->assertSuccessMessage(),
            'Sending data to the Analytics is not saved.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Sending data to the Analytics is saved.';
    }
}
