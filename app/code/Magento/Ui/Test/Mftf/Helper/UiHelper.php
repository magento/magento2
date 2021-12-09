<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Ui\Test\Mftf\Helper;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Magento\FunctionalTestingFramework\Helper\Helper;
use Magento\FunctionalTestingFramework\Module\MagentoWebDriver;
use Facebook\WebDriver\Exception\NoSuchWindowException;

/**
 * Class for MFTF helpers for Ui module.
 */
class UiHelper extends Helper
{
    private const COMPARISON_PATH_EXACT_MATCH = 'COMPARISON_PATH_EXACT_MATCH';
    private const COMPARISON_PATH_SUBSET_MATCH = 'COMPARISON_PATH_SUBSET_MATCH';

    private const COMPARISON_MATCH_TYPES = [
        self::COMPARISON_PATH_EXACT_MATCH,
        self::COMPARISON_PATH_SUBSET_MATCH
    ];

    public function switchToWindowWithUrlAndClosePrintDialogIfEncountered(
        string $expectedUrl,
        string $expectedUrlComparisonType
    ) {
        if (!in_array($expectedUrlComparisonType, self::COMPARISON_MATCH_TYPES)) {
            $this->fail('Expected URL comparison match type is not valid');
        }

        /** @var MagentoWebDriver $magentoWebDriver */
        $magentoWebDriver = $this->getModule('\Magento\FunctionalTestingFramework\Module\MagentoWebDriver');

        /** @var RemoteWebDriver $webDriver */
        $webDriver = $magentoWebDriver->webDriver;

        // Pressing escape blurs the window and "unfreezes" chromedriver when it switches context back to chrome::/print
        try {
            $magentoWebDriver->pressKey('body', [\Facebook\WebDriver\WebDriverKeys::ESCAPE]);
        } catch (NoSuchWindowException $e) {
            // This caught exception cannot be explained: no windows are closed as a result of this action; proceed
        }

        $evaluateIsWebDriverOnExpectedUrl = function () use ($webDriver, $expectedUrl, $expectedUrlComparisonType) {
            if ($expectedUrlComparisonType === self::COMPARISON_PATH_EXACT_MATCH) {
                $isWebDriverOnExpectedUrl = parse_url($webDriver->getCurrentURL(), PHP_URL_PATH) === $expectedUrl;
            } else { // COMPARISON_PATH_SUBSET_MATCH
                $isWebDriverOnExpectedUrl = strpos(
                    parse_url($webDriver->getCurrentURL(), PHP_URL_PATH),
                    $expectedUrl
                ) !== false;
            }

            return $isWebDriverOnExpectedUrl;
        };

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
                    // Print dialog closes yet exception is raised when it tries to get session context; proceed
                }

                continue;
            }

            $isWebDriverOnExpectedUrl = $evaluateIsWebDriverOnExpectedUrl();

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
}
