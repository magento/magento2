<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Test\TestStep;

use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Signifyd\Test\Page\Sandbox\SignifydNotifications;

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
     * Array of Signifyd config data.
     *
     * @var array
     */
    private $signifydData;

    /**
     * @param SignifydNotifications $signifydNotifications
     * @param array $signifydData
     */
    public function __construct(
        SignifydNotifications $signifydNotifications,
        array $signifydData
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
        $this->signifydNotifications->getWebhookGridBlock()->cleanupByTeam($this->signifydData['team']);
        $this->signifydNotifications->getWebhookAddBlock()->createWebhooks($this->signifydData);
    }
}
