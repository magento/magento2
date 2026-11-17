<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Paypal\Test\Mftf\Helper;

use Facebook\WebDriver\Exception\StaleElementReferenceException;
use Facebook\WebDriver\Exception\TimeoutException;
use Facebook\WebDriver\WebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverElement;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Magento\FunctionalTestingFramework\Helper\Helper;
use Magento\FunctionalTestingFramework\Module\MagentoWebDriver;
use RuntimeException;

/**
 * Recover a declined or timed-out Payflow Pro sandbox checkout with one additional submission.
 */
class PayflowProRetry extends Helper
{
    /**
     * Retry a decline or missing success once, only while Checkout remains ready for payment.
     *
     * @param string $successSelector
     * @param string $modalDeclineSelector
     * @param string $inlineDeclineSelector
     * @param string $dismissSelector
     * @param string $paymentMethodSelector
     * @param string $placeOrderSelector
     * @param array<string, string> $cardSelectors
     * @param array<string, string> $cardData
     * @return void
     */
    public function retryPayflowProOrder(
        string $successSelector,
        string $modalDeclineSelector,
        string $inlineDeclineSelector,
        string $dismissSelector,
        string $paymentMethodSelector,
        string $placeOrderSelector,
        array $cardSelectors,
        array $cardData
    ): void {
        /** @var MagentoWebDriver $module */
        $module = $this->getModule('\\' . MagentoWebDriver::class);
        $driver = $module->webDriver;
        $outcome = $this->waitForOutcome($driver, $successSelector, $modalDeclineSelector, $inlineDeclineSelector);
        if ($outcome === 'success') {
            return;
        }
        if ($outcome === 'modal-decline') {
            $this->restoreCardForm($module, $dismissSelector, $paymentMethodSelector, $cardSelectors, $cardData);
        }
        $module->waitForLoadingMaskToDisappear(120);
        if ($this->hasSuccessMessage($driver, $successSelector)) {
            return;
        }
        $this->assertCheckoutUrl($driver);
        if ($outcome === 'missing-success') {
            $this->assertTimeoutRetryAllowed(
                $driver,
                $modalDeclineSelector,
                $inlineDeclineSelector,
                $cardSelectors['number']
            );
        }
        $this->assertPlaceOrderAvailable($module, $placeOrderSelector);
        $this->debugSection('Payflow Pro retry', 'One additional sandbox submission after ' . $outcome);
        $module->click($placeOrderSelector);
        $module->waitForPageLoad(120);
        $module->waitForLoadingMaskToDisappear(120);
    }

    /**
     * Wait for a recognized outcome, tolerating elements replaced during checkout updates.
     *
     * @param WebDriver $driver
     * @param string $successSelector
     * @param string $modalDeclineSelector
     * @param string $inlineDeclineSelector
     * @return string
     */
    private function waitForOutcome(
        WebDriver $driver,
        string $successSelector,
        string $modalDeclineSelector,
        string $inlineDeclineSelector
    ): string {
        try {
            return $driver->wait(120)->until(
                function (WebDriver $driver) use ($successSelector, $modalDeclineSelector, $inlineDeclineSelector) {
                    try {
                        return $this->detectOutcome(
                            $driver,
                            $successSelector,
                            $modalDeclineSelector,
                            $inlineDeclineSelector
                        );
                    } catch (StaleElementReferenceException) {
                        return false;
                    }
                },
                'No Payflow Pro success message or recognized decline within 120 seconds.'
            );
        } catch (TimeoutException) {
            return 'missing-success';
        }
    }

    /**
     * Identify success or a recognized decline without accepting unrelated errors.
     *
     * @param WebDriver $driver
     * @param string $successSelector
     * @param string $modalDeclineSelector
     * @param string $inlineDeclineSelector
     * @return string|false
     */
    private function detectOutcome(
        WebDriver $driver,
        string $successSelector,
        string $modalDeclineSelector,
        string $inlineDeclineSelector
    ): string|false {
        if ($this->hasSuccessMessage($driver, $successSelector)) {
            return 'success';
        }
        $modal = $this->visibleElement($driver, $modalDeclineSelector);
        if ($modal !== null && trim($modal->getText()) === 'Your payment has been declined. Please try again.') {
            return 'modal-decline';
        }
        $inline = $this->visibleElement($driver, $inlineDeclineSelector);
        if ($inline !== null && str_contains($inline->getText(), 'Transaction has been declined')) {
            return 'inline-decline';
        }
        return false;
    }

    /**
     * Check the success text both before and immediately before any retry.
     *
     * @param WebDriver $driver
     * @param string $successSelector
     * @return bool
     */
    private function hasSuccessMessage(WebDriver $driver, string $successSelector): bool
    {
        $success = $this->visibleElement($driver, $successSelector);
        return $success !== null && trim($success->getText()) === 'Thank you for your purchase!';
    }

    /**
     * Refill the same card after the decline alert reloads Checkout.
     *
     * @param MagentoWebDriver $module
     * @param string $dismissSelector
     * @param string $paymentMethodSelector
     * @param array<string, string> $cardSelectors
     * @param array<string, string> $cardData
     * @return void
     */
    private function restoreCardForm(
        MagentoWebDriver $module,
        string $dismissSelector,
        string $paymentMethodSelector,
        array $cardSelectors,
        array $cardData
    ): void {
        $driver = $module->webDriver;
        $card = $this->visibleElement($driver, $cardSelectors['number']);
        if ($card === null) {
            throw new RuntimeException('Cannot recover Payflow Pro decline: card form is missing.');
        }
        $module->waitForElementClickable($dismissSelector, 30);
        $module->click($dismissSelector);
        // The decline alert reloads Checkout; wait for the old form to detach before refilling.
        $driver->wait(120)->until(WebDriverExpectedCondition::stalenessOf($card));
        $module->waitForPageLoad(120);
        $module->waitForLoadingMaskToDisappear(120);
        $module->waitForElementClickable($paymentMethodSelector, 30);
        $module->click($paymentMethodSelector);
        $module->waitForLoadingMaskToDisappear(120);
        $module->waitForElementVisible($cardSelectors['number'], 30);
        $module->fillField($cardSelectors['number'], $cardData['number']);
        $module->fillField($cardSelectors['cvv'], $cardData['cvv']);
        $module->selectOption($cardSelectors['month'], $cardData['month']);
        $module->selectOption($cardSelectors['year'], $cardData['year']);
    }

    /**
     * Refuse to resubmit after navigation away from Checkout.
     *
     * @param WebDriver $driver
     * @return void
     */
    private function assertCheckoutUrl(WebDriver $driver): void
    {
        $path = parse_url($driver->getCurrentURL(), PHP_URL_PATH);
        if (!is_string($path) || !preg_match('~/checkout/?$~', $path)) {
            throw new RuntimeException('Payflow Pro retry refused: browser is no longer on Checkout.');
        }
    }

    /**
     * Permit a timeout retry only with no visible error and an editable card form.
     *
     * @param WebDriver $driver
     * @param string $modalDeclineSelector
     * @param string $inlineDeclineSelector
     * @param string $cardNumberSelector
     * @return void
     */
    private function assertTimeoutRetryAllowed(
        WebDriver $driver,
        string $modalDeclineSelector,
        string $inlineDeclineSelector,
        string $cardNumberSelector
    ): void {
        $modal = $this->visibleElement($driver, $modalDeclineSelector);
        $inline = $this->visibleElement($driver, $inlineDeclineSelector);
        if ($modal !== null || ($inline !== null && trim($inline->getText()) !== '')) {
            throw new RuntimeException('Payflow Pro timeout retry refused: an unrecognized error is displayed.');
        }
        $card = $this->visibleElement($driver, $cardNumberSelector);
        if ($card === null || !$card->isEnabled() || $card->getAttribute('readonly') !== null) {
            throw new RuntimeException('Payflow Pro timeout retry refused: card form is not editable.');
        }
    }

    /**
     * Require an enabled Place Order button before the single additional submission.
     *
     * @param MagentoWebDriver $module
     * @param string $placeOrderSelector
     * @return void
     */
    private function assertPlaceOrderAvailable(MagentoWebDriver $module, string $placeOrderSelector): void
    {
        $module->waitForElementClickable($placeOrderSelector, 30);
        $button = $this->visibleElement($module->webDriver, $placeOrderSelector);
        if ($button === null || !$button->isEnabled()
            || preg_match('/(?:^|\s)disabled(?:\s|$)/', $button->getAttribute('class') ?? '')
        ) {
            throw new RuntimeException('Payflow Pro retry refused: Place Order is unavailable or disabled.');
        }
    }

    /**
     * Find a displayed element using a selector supplied by an MFTF Section.
     *
     * @param WebDriver $driver
     * @param string $selector
     * @return WebDriverElement|null
     */
    private function visibleElement(WebDriver $driver, string $selector): ?WebDriverElement
    {
        $by = str_starts_with($selector, '/')
            ? WebDriverBy::xpath($selector)
            : WebDriverBy::cssSelector($selector);
        foreach ($driver->findElements($by) as $element) {
            if ($element->isDisplayed()) {
                return $element;
            }
        }
        return null;
    }
}
