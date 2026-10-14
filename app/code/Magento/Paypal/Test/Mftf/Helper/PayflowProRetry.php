<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Paypal\Test\Mftf\Helper;

use Facebook\WebDriver\Exception\StaleElementReferenceException;
use Facebook\WebDriver\WebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverElement;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Magento\FunctionalTestingFramework\Helper\Helper;
use Magento\FunctionalTestingFramework\Module\MagentoWebDriver;
use RuntimeException;

/**
 * Recover a confirmed Payflow Pro sandbox decline with one additional submission.
 */
class PayflowProRetry extends Helper
{
    /**
     * Wait for success or a recognized decline, and retry only the latter.
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
            'No Payflow Pro success message or recognized decline within 120 seconds; refusing a blind retry.'
        );
        if ($outcome === 'success') {
            return;
        }

        $this->debugSection('Payflow Pro retry', 'One additional submission after ' . $outcome);
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

        $path = parse_url($driver->getCurrentURL(), PHP_URL_PATH);
        if (!is_string($path) || !preg_match('~/checkout/?$~', $path)) {
            throw new RuntimeException('Payflow Pro retry refused: browser is no longer on Checkout.');
        }
        $module->waitForLoadingMaskToDisappear(120);
        $module->waitForElementClickable($placeOrderSelector, 30);
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
