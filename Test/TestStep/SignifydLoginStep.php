<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Test\TestStep;

use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Signifyd\Test\Fixture\SignifydAccount;
use Magento\Signifyd\Test\Page\Sandbox\SignifydCases;
use Magento\Signifyd\Test\Page\Sandbox\SignifydLogin;

/**
 * Login into Signifyd console step.
 */
class SignifydLoginStep implements TestStepInterface
{
    /**
     * Signifyd account fixture.
     *
     * @var SignifydAccount
     */
    private $signifydAccount;

    /**
     * Signifyd login page.
     *
     * @var SignifydLogin
     */
    private $signifydLogin;

    /**
     * Signifyd cases page.
     *
     * @var SignifydCases
     */
    private $signifydCases;

    /**
     * @param SignifydAccount $signifydAccount
     * @param SignifydLogin $signifydLogin
     * @param SignifydCases $signifydCases
     */
    public function __construct(
        SignifydAccount $signifydAccount,
        SignifydLogin $signifydLogin,
        SignifydCases $signifydCases
    ) {
        $this->signifydAccount = $signifydAccount;
        $this->signifydLogin = $signifydLogin;
        $this->signifydCases = $signifydCases;
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $this->signifydLogin->open();
        $this->signifydLogin->getLoginBlock()->fill($this->signifydAccount);
        $this->signifydLogin->getLoginBlock()->login();

        $this->signifydCases->getCaseSearchBlock()->waitForLoading();
    }
}
