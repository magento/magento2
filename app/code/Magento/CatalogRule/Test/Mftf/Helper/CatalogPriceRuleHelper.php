<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogRule\Test\Mftf\Helper;

use Facebook\WebDriver\Remote\RemoteWebDriver as FacebookWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Magento\FunctionalTestingFramework\Helper\Helper;
use Magento\FunctionalTestingFramework\Module\MagentoWebDriver;

/**
 * Class for MFTF helpers for CatalogRule module.
 */
class CatalogPriceRuleHelper extends Helper
{
    /**
     * Delete all Catalog Price Rules obe by one.
     *
     * @param string $emptyRow
     * @param string $modalAceptButton
     * @param string $deleteButton
     * @param string $successMessageContainer
     * @param string $successMessage
     *
     * @return void
     */
    public function deleteAllCatalogPriceRules(
        string $firstNotEmptyRow,
        string $modalAcceptButton,
        string $deleteButton,
        string $successMessageContainer,
        string $successMessage
    ): void {
        try {
            /** @var MagentoWebDriver $webDriver */
            $magentoWebDriver = $this->getModule('\Magento\FunctionalTestingFramework\Module\MagentoWebDriver');
            /** @var FacebookWebDriver $webDriver */
            $webDriver = $magentoWebDriver->webDriver;
            $rows = $webDriver->findElements(WebDriverBy::cssSelector($firstNotEmptyRow));
            while (!empty($rows)) {
                $rows[0]->click();
                $magentoWebDriver->waitForPageLoad(30);
                $magentoWebDriver->click($deleteButton);
                $magentoWebDriver->waitForPageLoad(30);
                $magentoWebDriver->waitForElementVisible($modalAcceptButton, 10);
                $magentoWebDriver->waitForPageLoad(60);
                $magentoWebDriver->click($modalAcceptButton);
                $magentoWebDriver->waitForPageLoad(60);
                $magentoWebDriver->waitForLoadingMaskToDisappear();
                $magentoWebDriver->waitForElementVisible($successMessageContainer, 10);
                $magentoWebDriver->see($successMessage, $successMessageContainer);
                $rows = $webDriver->findElements(WebDriverBy::cssSelector($firstNotEmptyRow));
            }
        } catch (\Exception $e) {
            $this->fail($e->getMessage());
        }
    }
}
