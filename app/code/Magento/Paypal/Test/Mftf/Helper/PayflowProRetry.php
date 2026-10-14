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
     * @param string $cardNumberSelector
     * @param string $cvvSelector
     * @param string $monthSelector
     * @param string $yearSelector
     * @param string $cardNumber
     * @param string $cvv
     * @param string $month
     * @param string $year
     * @return void
     */
    public function retryPayflowProOrder(
        string $successSelector,
        string $modalDeclineSelector,
        string $inlineDeclineSelector,
        string $dismissSelector,
        string $paymentMethodSelector,
        string $placeOrderSelector,
        string $cardNumberSelector,
        string $cvvSelector,
        string $monthSelector,
        string $yearSelector,
        string $cardNumber,
        string $cvv,
        string $month,
        string $year
    ): void {
        /** @var MagentoWebDriver $module */
        $module = $this->getModule('\\' . MagentoWebDriver::class);
        $driver = $module->webDriver;
        try {
            $outcome = $driver->wait(120)->until(
                function (WebDriver $driver) use ($successSelector, $modalDeclineSelector, $inlineDeclineSelector) {
                    try {
                        $success = $this->visibleElement($driver, $successSelector);
                        if ($success !== null && trim($success->getText()) === 'Thank you for your purchase!') {
                            return 'success';
                        }
                        $modal = $this->visibleElement($driver, $modalDeclineSelector);
                        if ($modal !== null
                            && trim($modal->getText()) === 'Your payment has been declined. Please try again.'
                        ) {
                            return 'modal-decline';
                        }
                        $inline = $this->visibleElement($driver, $inlineDeclineSelector);
                        if ($inline !== null && str_contains($inline->getText(), 'Transaction has been declined')) {
                            return 'inline-decline';
                        }
                    } catch (StaleElementReferenceException $exception) {
                        return false;
                    }
                    return false;
                },
                'No Payflow Pro success message or recognized decline within 120 seconds.'
            );
        } catch (TimeoutException $exception) {
            $outcome = 'missing-success';
        }
        if ($outcome === 'success') {
            return;
        }

        if ($outcome === 'modal-decline') {
            $card = $this->visibleElement($driver, $cardNumberSelector);
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
            $module->waitForElementVisible($cardNumberSelector, 30);
            $module->fillField($cardNumberSelector, $cardNumber);
            $module->fillField($cvvSelector, $cvv);
            $module->selectOption($monthSelector, $month);
            $module->selectOption($yearSelector, $year);
        }

        $module->waitForLoadingMaskToDisappear(120);
        $success = $this->visibleElement($driver, $successSelector);
        if ($success !== null && trim($success->getText()) === 'Thank you for your purchase!') {
            return;
        }
        $path = parse_url($driver->getCurrentURL(), PHP_URL_PATH);
        if (!is_string($path) || !preg_match('~/checkout/?$~', $path)) {
            throw new RuntimeException('Payflow Pro retry refused: browser is no longer on Checkout.');
        }
        if ($outcome === 'missing-success') {
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
        $module->waitForElementClickable($placeOrderSelector, 30);
        $button = $this->visibleElement($driver, $placeOrderSelector);
        if ($button === null || !$button->isEnabled()
            || preg_match('/(?:^|\s)disabled(?:\s|$)/', $button->getAttribute('class') ?? '')
        ) {
            throw new RuntimeException('Payflow Pro retry refused: Place Order is unavailable or disabled.');
        }
        $this->debugSection('Payflow Pro retry', 'One additional sandbox submission after ' . $outcome);
        $module->click($placeOrderSelector);
        $module->waitForPageLoad(120);
        $module->waitForLoadingMaskToDisappear(120);
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
