<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Mftf\Helper;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Magento\FunctionalTestingFramework\Helper\Helper;
use Facebook\WebDriver\Exception\NoSuchWindowException;

/**
 * Class for MFTF helpers for Sales module.
 */
class SalesHelper extends Helper
{
    private const COMPARISON_PATH_EXACT_MATCH = 'COMPARISON_PATH_EXACT_MATCH';
    private const COMPARISON_PATH_SUBSET_MATCH = 'COMPARISON_PATH_SUBSET_MATCH';

    private const COMPARISON_MATCH_TYPES = [
        self::COMPARISON_PATH_EXACT_MATCH,
        self::COMPARISON_PATH_SUBSET_MATCH
    ];

    /**
     * Iterate through all available window handles and attach webdriver to window matching $expectedUrl.
     * If print dialog is found, close it to prevent selenium from hanging and becoming unresponsive.
     *
     * @param string $expectedUrl
     * @param string $expectedUrlComparisonType
     * @throws \Codeception\Exception\ModuleException
     */
    public function switchToWindowWithUrlAndClosePrintDialogIfEncountered(
        string $expectedUrl,
        string $expectedUrlComparisonType
    ) {
        if (!in_array($expectedUrlComparisonType, self::COMPARISON_MATCH_TYPES)) {
            $this->fail('Expected URL comparison match type is not valid');
        }

        $magentoWebDriver = $this->getModule('\Magento\FunctionalTestingFramework\Module\MagentoWebDriver');

        $webDriver = $magentoWebDriver->webDriver;

        // Pressing escape blurs the window and "unfreezes" chromedriver when it switches context back to chrome://print
        try {
            $magentoWebDriver->pressKey('body', [\Facebook\WebDriver\WebDriverKeys::ESCAPE]);
        } catch (NoSuchWindowException $e) {
            // This caught exception cannot be explained; no windows are closed as a result of this action; proceed
        }

        $targetWindowHandle = null;
        $availableWindowHandles = $webDriver->getWindowHandles();

        foreach ($availableWindowHandles as $availableWindowHandle) {
            $webDriver->switchTo()->window($availableWindowHandle);

            if ($webDriver->getCurrentURL() === 'chrome://print/') {
                try {
                    // this escape press actually closes the print dialog
                    // the previous escape press is necessary for this press to close the dialog
                    $magentoWebDriver->pressKey('body', [\Facebook\WebDriver\WebDriverKeys::ESCAPE]);
                } catch (NoSuchWindowException $e) {
                    // Print dialog successfully closes when requested in selenium,
                    // yet missing window message is sent back in the response
                    // when it evaluates the value on the element after the press; proceed
                }

                // selenium is now effectively detached from any window; attach to an available window handle in case
                // "fail" method is called and MFTF "after"/teardown steps need to be executed
                $webDriver->switchTo()->window($webDriver->getWindowHandles()[0]);

                continue;
            }

            $isWebDriverOnExpectedUrl = $this->evaluateIsWebDriverOnExpectedUrl(
                $webDriver,
                $expectedUrl,
                $expectedUrlComparisonType
            );

            if ($isWebDriverOnExpectedUrl) {
                $targetWindowHandle = $webDriver->getWindowHandle();
            }
        }

        if (!$targetWindowHandle) {
            $this->fail('Could not find window handle with requested expected url');
        }

        // switch to target window handle
        $webDriver->switchTo()->window($targetWindowHandle);
    }

    /**
     * Is $webDriver currently attached to a window that matches $expectedUrl?
     *
     * @param RemoteWebDriver $webDriver
     * @param string $expectedUrl
     * @param string $expectedUrlComparisonType
     * @return bool
     */
    private function evaluateIsWebDriverOnExpectedUrl(
        RemoteWebDriver $webDriver,
        string $expectedUrl,
        string $expectedUrlComparisonType
    ): bool {
        $currentWebDriverUrlPath = $webDriver->getCurrentURL() !== null ?
            parse_url($webDriver->getCurrentURL(), PHP_URL_PATH) : '';

        switch ($expectedUrlComparisonType) {
            case self::COMPARISON_PATH_EXACT_MATCH:
                $isWebDriverOnExpectedUrl = $currentWebDriverUrlPath === $expectedUrl;
                break;
            case self::COMPARISON_PATH_SUBSET_MATCH:
            default:
                $isWebDriverOnExpectedUrl = strpos($currentWebDriverUrlPath, $expectedUrl) !== false;
                break;
        }

        return $isWebDriverOnExpectedUrl;
    }
}
