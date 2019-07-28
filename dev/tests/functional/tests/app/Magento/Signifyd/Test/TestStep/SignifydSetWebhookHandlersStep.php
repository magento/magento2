<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Test\TestStep;

use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Signifyd\Test\Fixture\SignifydData;
use Magento\Signifyd\Test\Page\SignifydConsole\SignifydNotifications;

/**
 * Set webhook handlers in Signifyd console step.
 */
class SignifydSetWebhookHandlersStep implements TestStepInterface
{
    /**
     * Signifyd Notifications page.
     *
     * @var SignifydNotifications
     */
    private $signifydNotifications;

    /**
     * Signifyd data fixture.
     *
     * @var array
     */
    private $signifydData;

    /**
     * @param SignifydNotifications $signifydNotifications
     * @param SignifydData $signifydData
     */
    public function __construct(
        SignifydNotifications $signifydNotifications,
        SignifydData $signifydData
    ) {
        $this->signifydNotifications = $signifydNotifications;
        $this->signifydData = $signifydData;
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $this->signifydNotifications->open();
        $this->signifydNotifications->getWebhooksBlock()
            ->create($this->signifydData->getTeam());
    }

    /**
     * Removes webhooks if test fails, or in the end of variation execution.
     *
     * @return void
     */
    public function cleanup()
    {
        $this->signifydNotifications->open();
        $this->signifydNotifications->getWebhooksBlock()
            ->cleanup($this->signifydData->getTeam());
    }
}
