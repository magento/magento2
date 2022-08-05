<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Mftf\Helper;

use Facebook\WebDriver\Remote\RemoteWebDriver as FacebookWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Magento\FunctionalTestingFramework\Helper\Helper;
use Magento\FunctionalTestingFramework\Module\MagentoWebDriver;

/**
 * Class for MFTF helpers for Catalog module.
 */
class CatalogHelper extends Helper
{
    /**
     * Delete all product attributes one by one.
     *
     * @param string $notEmptyRow
     * @param string $modalAcceptButton
     * @param string $deleteButton
     * @param string $successMessageContainer
     * @param string $successMessage
     * @retrun void
     */
    public function deleteAllProductAttributesOneByOne(
        string $notEmptyRow,
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
            $gridRows = $webDriver->findElements(WebDriverBy::cssSelector($notEmptyRow));
            while (!empty($gridRows)) {
                $gridRows[0]->click();
                $magentoWebDriver->waitForPageLoad(30);
                $magentoWebDriver->click($deleteButton);
                $magentoWebDriver->waitForPageLoad(30);
                $magentoWebDriver->waitForElementVisible($modalAcceptButton);
                $magentoWebDriver->click($modalAcceptButton);
                $magentoWebDriver->waitForPageLoad(60);
                $magentoWebDriver->waitForElementVisible($successMessageContainer);
                $magentoWebDriver->see($successMessage, $successMessageContainer);
                $gridRows = $webDriver->findElements(WebDriverBy::cssSelector($notEmptyRow));
            }
        } catch (\Exception $e) {
            $this->fail($e->getMessage());
        }
    }
}
