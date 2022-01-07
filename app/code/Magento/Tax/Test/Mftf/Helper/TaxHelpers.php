<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Tax\Test\Mftf\Helper;

use Facebook\WebDriver\Remote\RemoteWebDriver as FacebookWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Magento\FunctionalTestingFramework\Helper\Helper;
use Magento\FunctionalTestingFramework\Module\MagentoWebDriver;

/**
 * Class for MFTF helpers for Tax module.
 */
class TaxHelpers extends Helper
{
    /**
     * Delete all specified Tax Rules one by one from the Tax Zones and Rates page.
     *
     * @param string $rowsToDelete
     * @param string $deleteButton
     * @param string $modalAcceptButton
     * @param string $successMessage
     * @param string $successMessageContainer
     *
     * @return void
     */
    public function deleteAllSpecifiedTaxRuleRows(
        string $rowsToDelete,
        string $deleteButton,
        string $modalAcceptButton,
        string $successMessage,
        string $successMessageContainer
    ): void {
        try {
            /** @var MagentoWebDriver $webDriver */
            $magentoWebDriver = $this->getModule('\Magento\FunctionalTestingFramework\Module\MagentoWebDriver');
            /** @var FacebookWebDriver $webDriver */
            $webDriver = $magentoWebDriver->webDriver;

            $magentoWebDriver->waitForPageLoad(30);
            $rows = $webDriver->findElements(WebDriverBy::xpath($rowsToDelete));
            while (!empty($rows)) {
                $rows[0]->click();
                $magentoWebDriver->waitForPageLoad(30);
                $magentoWebDriver->waitForElementVisible($deleteButton, 10);
                $magentoWebDriver->click($deleteButton);
                $magentoWebDriver->waitForPageLoad(30);
                $magentoWebDriver->waitForElementVisible($modalAcceptButton, 10);
                $magentoWebDriver->click($modalAcceptButton);
                $magentoWebDriver->waitForPageLoad(60);
                $magentoWebDriver->waitForText($successMessage, 10, $successMessageContainer);
                $rows = $webDriver->findElements(WebDriverBy::xpath($rowsToDelete));
            }
        } catch (\Exception $exception) {
            $this->fail($exception->getMessage());
        }
    }
}
