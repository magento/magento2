<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Test\TestStep;

use Magento\Backend\Test\Page\Adminhtml\Dashboard;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Close access error modal message.
 */
class CloseErrorAlertStep implements TestStepInterface
{
    /**
     * @var Dashboard
     */
    private $dashboard;

    /**
     * @var BrowserInterface
     */
    private $browser;

    /**
     * @param Dashboard $dashboard
     * @param BrowserInterface $browser
     */
    public function __construct(
        Dashboard $dashboard,
        BrowserInterface $browser
    ) {
        $this->dashboard = $dashboard;
        $this->browser = $browser;
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $modalMessage = $this->dashboard->getModalMessage();
        try {
            $this->browser->waitUntil(
                function () use ($modalMessage) {
                    return $modalMessage->isVisible() ? true : null;
                }
            );
            $modalMessage->acceptAlert();
        } catch (\PHPUnit_Extensions_Selenium2TestCase_WebDriverException $e) {
            //There is no modal to accept.
        }
    }
}
