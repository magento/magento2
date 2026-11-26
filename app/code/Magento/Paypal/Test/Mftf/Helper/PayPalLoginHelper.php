<?php

/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Paypal\Test\Mftf\Helper;

use Magento\FunctionalTestingFramework\Helper\Helper;
use Magento\FunctionalTestingFramework\Module\MagentoActionProxies;
use Magento\FunctionalTestingFramework\Module\MagentoWebDriver;

/**
 * Recover the email step when PayPal clears the submitted login.
 */
class PayPalLoginHelper extends Helper
{
    /**
     * Refill the email before retrying Next without exposing decrypted credentials.
     *
     * @param string $emailSelector
     * @param string $passwordSelector
     * @param string $credentialKey
     * @return void
     */
    public function refillEmailIfStillOnEmailStep(
        string $emailSelector,
        string $passwordSelector,
        string $credentialKey
    ): void {
        /** @var MagentoWebDriver $magentoWebDriver */
        $magentoWebDriver = $this->getModule('\\' . MagentoWebDriver::class);
        foreach ($magentoWebDriver->_findElements($passwordSelector) as $passwordElement) {
            if ($passwordElement->isDisplayed()) {
                return;
            }
        }
        $elements = $magentoWebDriver->_findElements($emailSelector);

        if (empty($elements) || !$elements[0]->isDisplayed()) {
            return;
        }

        /** @var MagentoActionProxies $actionProxies */
        $actionProxies = $this->getModule('\\' . MagentoActionProxies::class);
        $magentoWebDriver->fillSecretField($emailSelector, $actionProxies->getSecret($credentialKey));
    }

    /**
     * Retry Next only while the email step has an actionable Next button.
     *
     * @param string $nextSelector
     * @param string $passwordSelector
     * @return void
     */
    public function clickNextIfStillOnEmailStep(string $nextSelector, string $passwordSelector): void
    {
        /** @var MagentoWebDriver $magentoWebDriver */
        $magentoWebDriver = $this->getModule('\\' . MagentoWebDriver::class);
        foreach ($magentoWebDriver->_findElements($passwordSelector) as $passwordElement) {
            if ($passwordElement->isDisplayed()) {
                return;
            }
        }
        $elements = $magentoWebDriver->_findElements($nextSelector);

        if (empty($elements) || !$elements[0]->isDisplayed() || !$elements[0]->isEnabled()) {
            return;
        }

        $magentoWebDriver->click($nextSelector);
    }
}
