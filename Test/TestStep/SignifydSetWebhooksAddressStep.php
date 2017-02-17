<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Test\TestStep;

use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Signifyd\Test\Fixture\SandboxMerchant;
use Magento\Signifyd\Test\Page\Sandbox\SignifydCases;
use Magento\Signifyd\Test\Page\Sandbox\SignifydLogin;
use Magento\Signifyd\Test\Page\Sandbox\SignifydNotifications;

/**
 * Class SignifydSetWebhooksAddressStep
 */
class SignifydSetWebhooksAddressStep implements TestStepInterface
{
    /**
     * @var SandboxMerchant
     */
    private $sandboxMerchant;

    /**
     * @var SignifydLogin
     */
    private $signifydLogin;

    /**
     * @var SignifydCases
     */
    private $signifydCases;

    /**
     * @var SignifydNotifications
     */
    private $signifydNotifications;

    /**
     * @param SandboxMerchant $sandboxMerchant
     * @param SignifydLogin $signifydLogin
     * @param SignifydCases $signifydCases
     * @param SignifydNotifications $signifydNotifications
     */
    public function __construct(
        SandboxMerchant $sandboxMerchant,
        SignifydLogin $signifydLogin,
        SignifydCases $signifydCases,
        SignifydNotifications $signifydNotifications
    ) {
        $this->sandboxMerchant = $sandboxMerchant;
        $this->signifydLogin = $signifydLogin;
        $this->signifydCases = $signifydCases;
        $this->signifydNotifications = $signifydNotifications;
    }

    /**
     * Run step flow
     *
     * @return void
     */
    public function run()
    {
        $this->signifydLogin->open();
        $this->signifydLogin->getLoginBlock()->fill($this->sandboxMerchant);
        $this->signifydLogin->getLoginBlock()->sandboxLogin();

        $this->signifydCases->getCaseSearchBlock()->waitForElementVisible('#queueSearchBar');

        $this->signifydNotifications->open();
        $this->signifydNotifications->getWebhooksBlock()->cleanup();
        $this->signifydNotifications->getWebhookAddBlock()->createWebhooks();
    }
}
