<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Paypal\Test\Mftf\Helper;

use Facebook\WebDriver\Exception\NoSuchWindowException;
use Facebook\WebDriver\Exception\StaleElementReferenceException;
use Facebook\WebDriver\Exception\TimeoutException;
use Magento\FunctionalTestingFramework\Helper\Helper;
use Magento\FunctionalTestingFramework\Module\MagentoActionProxies;
use Magento\FunctionalTestingFramework\Module\MagentoWebDriver;
use RuntimeException;

/**
 * Bounded sandbox-only recovery for the Payflow Advanced shipping-refund scenario.
 */
class PayPalAdvancedRefundHelper extends Helper
{
    private const MAX_REFUND_ATTEMPTS = 11;
    private const MAX_LOGIN_ATTEMPTS = 3;
    private const REFUND_DECLINE =
        'The PayPal gateway rejected the request. Declined: 10001-Internal Error';
    private const SELECTORS = [
        'productOption', 'productQty', 'addToCart', 'addToCartDisabled', 'addingToCart',
        'addedToCart', 'storefrontSuccess', 'proceedToCheckout', 'shippingNext', 'paymentMethod',
        'continuePayPal', 'email', 'next', 'password', 'usePassword', 'login', 'approve',
        'checkoutSuccess', 'orderLink', 'invoice', 'pageTitle', 'submitInvoice', 'success', 'error',
        'invoiceTab', 'viewInvoice', 'creditMemo', 'refundQty', 'updateRefundQty', 'refund',
    ];

    /**
     * Purchase a new sandbox order on each refund decline; return immediately on success.
     *
     * @param string $productUrl
     * @param string $productName
     * @param string $optionLabel
     * @param string $cartUrl
     * @param string $adminOrderUrl
     * @param string $paypalEmail MFTF credential key.
     * @param string $paypalPassword MFTF credential key.
     * @param string $selectorsJson Selectors resolved from existing MFTF sections by the action group.
     * @return void
     */
    public function purchaseAndRefundWithRetry(
        string $productUrl,
        string $productName,
        string $optionLabel,
        string $cartUrl,
        string $adminOrderUrl,
        string $paypalEmail,
        string $paypalPassword,
        string $selectorsJson
    ): void {
        $selectors = $this->resolveSelectors($selectorsJson);
        /** @var MagentoWebDriver $driver */
        $driver = $this->getModule('\\' . MagentoWebDriver::class);
        for ($attempt = 1; $attempt <= self::MAX_REFUND_ATTEMPTS; $attempt++) {
            $this->assertSandbox($driver);
            $this->debugSection(
                'PayPal sandbox',
                'Purchase/refund attempt ' . $attempt . '/' . self::MAX_REFUND_ATTEMPTS
            );
            $this->checkout(
                $driver,
                $selectors,
                $productUrl,
                $productName,
                $optionLabel,
                $cartUrl,
                $paypalEmail,
                $paypalPassword
            );
            $orderLink = $driver->grabAttributeFrom($selectors['orderLink'], 'href');
            if (!is_string($orderLink) || !preg_match('~/order_id/(\d+)/~', $orderLink, $matches)) {
                throw new RuntimeException('Cannot resolve the new order entity ID from the checkout success link.');
            }
            $this->invoiceAndRefund($driver, $selectors, $adminOrderUrl . $matches[1] . '/');
            $outcome = $this->waitForState(
                $driver,
                ['error' => $selectors['error'], 'success' => $selectors['success']]
            );
            if ($outcome === 'success') {
                $driver->waitForText('You created the credit memo', 60, $selectors['success']);
                return;
            }
            $error = trim(preg_replace('/\s+/', ' ', $driver->grabTextFrom($selectors['error'])));
            if ($error !== self::REFUND_DECLINE) {
                throw new RuntimeException('PayPal refund failed without retry: ' . $error);
            }
            $this->debugSection('PayPal sandbox refund declined', $error);
            $driver->makeScreenshot('payflow-advanced-refund-declined-attempt-' . $attempt);
            if ($attempt === self::MAX_REFUND_ATTEMPTS) {
                throw new RuntimeException(
                    'PayPal sandbox refund declined in all ' . self::MAX_REFUND_ATTEMPTS . ' attempts: ' . $error
                );
            }
        }
    }

    /**
     * Validate the section selectors supplied by the action group.
     *
     * @param string $selectorsJson
     * @return array<string, string>
     */
    private function resolveSelectors(string $selectorsJson): array
    {
        $selectors = json_decode($selectorsJson, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($selectors)) {
            throw new RuntimeException('PayPal scenario selectors must be an object.');
        }
        $validated = [];
        foreach (self::SELECTORS as $name) {
            if (!isset($selectors[$name]) || !is_string($selectors[$name]) || $selectors[$name] === '') {
                throw new RuntimeException('Missing PayPal scenario selector: ' . $name);
            }
            $validated[$name] = $selectors[$name];
        }
        return $validated;
    }

    /**
     * Payflow Express shares the enabled Payflow Advanced method's sandbox configuration.
     *
     * @param MagentoWebDriver $driver
     * @return void
     */
    private function assertSandbox(MagentoWebDriver $driver): void
    {
        $path = 'payment/payflow_advanced/sandbox_flag';
        if (trim($driver->magentoCLI('config:show ' . $path)) !== '1') {
            throw new RuntimeException('Refusing PayPal retries: cannot confirm sandbox mode for ' . $path);
        }
    }

    /**
     * Repeat only the original add-to-cart and Payflow checkout flow, using the existing fixture.
     *
     * @param MagentoWebDriver $driver
     * @param array<string, string> $selectors
     * @param string $productUrl
     * @param string $productName
     * @param string $optionLabel
     * @param string $cartUrl
     * @param string $paypalEmail
     * @param string $paypalPassword
     * @return void
     */
    private function checkout(
        MagentoWebDriver $driver,
        array $selectors,
        string $productUrl,
        string $productName,
        string $optionLabel,
        string $cartUrl,
        string $paypalEmail,
        string $paypalPassword
    ): void {
        $driver->amOnPage($productUrl);
        $driver->waitForPageLoad(60);
        $driver->waitForElementClickable($selectors['productOption'], 60);
        $driver->selectOption($selectors['productOption'], $optionLabel);
        $driver->fillField($selectors['productQty'], '1');
        $driver->waitForElementNotVisible($selectors['addToCartDisabled'], 60);
        $driver->scrollTo($selectors['addToCart']);
        $this->clickAndWait($driver, $selectors['addToCart']);
        $driver->waitForElementNotVisible($selectors['addingToCart'], 60);
        $driver->waitForElementNotVisible($selectors['addedToCart'], 60);
        $driver->waitForText(
            'You added ' . $productName . ' to your shopping cart.',
            60,
            $selectors['storefrontSuccess']
        );
        $driver->amOnPage($cartUrl);
        $driver->waitForPageLoad(60);
        $this->clickAndWait($driver, $selectors['proceedToCheckout']);
        $this->clickAndWait($driver, $selectors['shippingNext']);
        $this->clickAndWait($driver, $selectors['paymentMethod']);
        $storefrontWindow = $driver->webDriver->getWindowHandle();
        $this->clickAndWait($driver, $selectors['continuePayPal']);
        $driver->switchToNextTab();
        $driver->waitForPageLoad(60);
        $paypalWindow = $driver->webDriver->getWindowHandle();
        $url = $driver->grabFromCurrentUrl();
        $host = parse_url($url, PHP_URL_HOST);
        if (parse_url($url, PHP_URL_SCHEME) !== 'https'
            || !is_string($host)
            || ($host !== 'sandbox.paypal.com' && !str_ends_with($host, '.sandbox.paypal.com'))
        ) {
            throw new RuntimeException('Refusing PayPal login or approval outside the HTTPS PayPal sandbox.');
        }
        $state = $this->waitForState(
            $driver,
            ['approval' => $selectors['approve'], 'email' => $selectors['email']]
        );
        if ($state === 'email') {
            $this->login($driver, $selectors, $paypalEmail, $paypalPassword);
        }
        $driver->scrollTo($selectors['approve']);
        $driver->waitForElementClickable($selectors['approve'], 60);
        $driver->click($selectors['approve']);
        $driver->webDriver->switchTo()->window($storefrontWindow);
        $driver->waitForPageLoad(60);
        $driver->waitForElementVisible($selectors['checkoutSuccess'], 60);
        $driver->waitForElementVisible($selectors['orderLink'], 60);
        $this->closePayPalWindow($driver, $storefrontWindow, $paypalWindow);
    }

    /**
     * Close only a separate PayPal popup, never the storefront's own window.
     *
     * @param MagentoWebDriver $driver
     * @param string $storefrontWindow
     * @param string $paypalWindow
     * @return void
     */
    private function closePayPalWindow(
        MagentoWebDriver $driver,
        string $storefrontWindow,
        string $paypalWindow
    ): void {
        if ($paypalWindow === $storefrontWindow) {
            return;
        }
        try {
            if (in_array($paypalWindow, $driver->webDriver->getWindowHandles(), true)) {
                $driver->webDriver->switchTo()->window($paypalWindow);
                $driver->webDriver->close();
            }
        } catch (NoSuchWindowException $exception) {
            $this->debugSection('PayPal popup cleanup', 'PayPal already closed its checkout popup.');
        } finally {
            $driver->webDriver->switchTo()->window($storefrontWindow);
        }
    }

    /**
     * Refill the email before every bounded retry, supporting both PayPal login layouts.
     *
     * @param MagentoWebDriver $driver
     * @param array<string, string> $selectors
     * @param string $email
     * @param string $password
     * @return void
     */
    private function login(MagentoWebDriver $driver, array $selectors, string $email, string $password): void
    {
        /** @var MagentoActionProxies $proxies */
        $proxies = $this->getModule('\\' . MagentoActionProxies::class);
        $email = $proxies->getSecret($email);
        $password = $proxies->getSecret($password);
        for ($attempt = 1; $attempt <= self::MAX_LOGIN_ATTEMPTS; $attempt++) {
            $driver->waitForElementClickable($selectors['email'], 60);
            $driver->click($selectors['email']);
            $driver->fillSecretField($selectors['email'], $email);
            if ($this->visible($driver, $selectors['next'])) {
                $this->clickAndWait($driver, $selectors['next']);
            }
            try {
                $state = $this->waitForState(
                    $driver,
                    ['password' => $selectors['password'], 'alternative' => $selectors['usePassword']],
                    20
                );
            } catch (TimeoutException $exception) {
                if ($attempt === self::MAX_LOGIN_ATTEMPTS
                    || !$this->visible($driver, $selectors['email'])
                    || !$this->visible($driver, $selectors['next'])
                ) {
                    throw $exception;
                }
                $this->debugSection(
                    'PayPal login retry',
                    'Email step stalled; refilling before attempt ' . ($attempt + 1)
                );
                continue;
            }
            if ($state === 'alternative') {
                $this->clickAndWait($driver, $selectors['usePassword']);
            }
            break;
        }
        $driver->waitForElementClickable($selectors['password'], 60);
        $driver->fillSecretField($selectors['password'], $password);
        $this->clickAndWait($driver, $selectors['login']);
        $driver->waitForElementClickable($selectors['approve'], 60);
    }

    /**
     * Invoice the newly created order, then submit exactly one shipping-only online refund.
     *
     * @param MagentoWebDriver $driver
     * @param array<string, string> $selectors
     * @param string $orderUrl
     * @return void
     */
    private function invoiceAndRefund(MagentoWebDriver $driver, array $selectors, string $orderUrl): void
    {
        $driver->amOnPage($orderUrl);
        $driver->waitForPageLoad(60);
        $this->clickAndWait($driver, $selectors['invoice']);
        $driver->waitForText('New Invoice', 60, $selectors['pageTitle']);
        $this->clickAndWait($driver, $selectors['submitInvoice']);
        $driver->waitForText('The invoice has been created.', 60, $selectors['success']);
        $driver->seeInCurrentUrl($orderUrl);
        $this->clickAndWait($driver, $selectors['invoiceTab']);
        $this->clickAndWait($driver, $selectors['viewInvoice']);
        $this->clickAndWait($driver, $selectors['creditMemo']);
        $driver->waitForElementVisible($selectors['refundQty'], 60);
        $driver->fillField($selectors['refundQty'], '0');
        $this->clickAndWait($driver, $selectors['updateRefundQty']);
        $this->clickAndWait($driver, $selectors['refund']);
    }

    /**
     * Wait for a clickable target before interacting and for navigation afterward.
     *
     * @param MagentoWebDriver $driver
     * @param string $selector
     * @return void
     */
    private function clickAndWait(MagentoWebDriver $driver, string $selector): void
    {
        $driver->waitForElementClickable($selector, 60);
        $driver->click($selector);
        $driver->waitForPageLoad(60);
    }

    /**
     * Wait for one of the explicit states without swallowing browser or application failures.
     *
     * @param MagentoWebDriver $driver
     * @param array<string, string> $states
     * @param int $timeout
     * @return string
     */
    private function waitForState(MagentoWebDriver $driver, array $states, int $timeout = 60): string
    {
        return $driver->webDriver->wait($timeout)->until(
            function () use ($driver, $states) {
                foreach ($states as $state => $selector) {
                    if ($this->visible($driver, $selector)) {
                        return $state;
                    }
                }
                return false;
            },
            'PayPal scenario did not reach an expected state: ' . implode(', ', array_keys($states))
        );
    }

    /**
     * Ignore only a stale element during polling; the next poll resolves fresh elements.
     *
     * @param MagentoWebDriver $driver
     * @param string $selector
     * @return bool
     */
    private function visible(MagentoWebDriver $driver, string $selector): bool
    {
        try {
            foreach ($driver->_findElements($selector) as $element) {
                if ($element->isDisplayed()) {
                    return true;
                }
            }
        } catch (StaleElementReferenceException $exception) {
            return false;
        }
        return false;
    }
}
