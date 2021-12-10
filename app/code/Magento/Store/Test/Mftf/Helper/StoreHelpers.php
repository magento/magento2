<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Test\Mftf\Helper;

use Facebook\WebDriver\Remote\RemoteWebDriver as FacebookWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Magento\FunctionalTestingFramework\Helper\Helper;
use Magento\FunctionalTestingFramework\Module\MagentoWebDriver;

/**
 * Class for MFTF helpers for Store module.
 */
class StoreHelpers extends Helper
{
    /**
     * Delete all specified Websites one by one from the admin Stores page.
     *
     * @param string $websitesToDelete
     * @param string $deleteButton
     * @param string $createDbBackupButton
     * @param string $successMessage
     * @param string $successMessageContainer
     *
     * @return void
     */
    public function deleteAllSpecifiedWebsites(
        string $websitesToDelete,
        string $deleteButton,
        string $createDbBackupButton,
        string $successMessage,
        string $successMessageContainer
    ): void {
        try {
            $magentoWebDriver = $this->getModule('\Magento\FunctionalTestingFramework\Module\MagentoWebDriver');
            /** @var FacebookWebDriver $webDriver */
            $webDriver = $magentoWebDriver->webDriver;

            $magentoWebDriver->waitForPageLoad(30);
            $websites = $webDriver->findElements(WebDriverBy::xpath($websitesToDelete));
            while (!empty($websites)) {
                $websites[0]->click();
                $magentoWebDriver->waitForPageLoad(30);
                $magentoWebDriver->waitForElementVisible($deleteButton, 10);
                $magentoWebDriver->click($deleteButton);
                $magentoWebDriver->waitForPageLoad(30);
                $magentoWebDriver->waitForElementVisible($createDbBackupButton, 10);
                $magentoWebDriver->selectOption($createDbBackupButton, 'No');
                $magentoWebDriver->click($deleteButton);
                $magentoWebDriver->waitForPageLoad(60);
                $magentoWebDriver->waitForText($successMessage, 10, $successMessageContainer);
                $websites = $webDriver->findElements(WebDriverBy::xpath($websitesToDelete));
            }
        } catch (\Exception $exception) {
            $this->fail($exception->getMessage());
        }
    }

    /**
     * Delete all specified Stores one by one from the admin Stores page.
     *
     * @param string $storesToDelete
     * @param string $deleteButton
     * @param string $createDbBackupButton
     * @param string $successMessage
     * @param string $successMessageContainer
     *
     * @return void
     */
    public function deleteAllSpecifiedStores(
        string $storesToDelete,
        string $deleteButton,
        string $createDbBackupButton,
        string $successMessage,
        string $successMessageContainer
    ): void {
        try {
            $magentoWebDriver = $this->getModule('\Magento\FunctionalTestingFramework\Module\MagentoWebDriver');
            /** @var FacebookWebDriver $webDriver */
            $webDriver = $magentoWebDriver->webDriver;

            $magentoWebDriver->waitForPageLoad(30);
            $stores = $webDriver->findElements(WebDriverBy::xpath($storesToDelete));
            while (!empty($stores)) {
                $stores[0]->click();
                $magentoWebDriver->waitForPageLoad(30);
                $magentoWebDriver->waitForElementVisible($deleteButton, 10);
                $magentoWebDriver->click($deleteButton);
                $magentoWebDriver->waitForPageLoad(30);
                $magentoWebDriver->waitForElementVisible($createDbBackupButton, 10);
                $magentoWebDriver->selectOption($createDbBackupButton, 'No');
                $magentoWebDriver->click($deleteButton);
                $magentoWebDriver->waitForPageLoad(60);
                $magentoWebDriver->waitForText($successMessage, 10, $successMessageContainer);
                $stores = $webDriver->findElements(WebDriverBy::xpath($storesToDelete));
            }
        } catch (\Exception $exception) {
            $this->fail($exception->getMessage());
        }
    }

    /**
     * Delete all specified Store Views one by one from the admin Stores page.
     *
     * @param string $storeViewsToDelete
     * @param string $deleteButton
     * @param string $createDbBackupButton
     * @param string $successMessage
     * @param string $successMessageContainer
     *
     * @return void
     */
    public function deleteAllSpecifiedStoreViews(
        string $storeViewsToDelete,
        string $deleteButton,
        string $createDbBackupButton,
        string $successMessage,
        string $successMessageContainer
    ): void {
        try {
            $magentoWebDriver = $this->getModule('\Magento\FunctionalTestingFramework\Module\MagentoWebDriver');
            /** @var FacebookWebDriver $webDriver */
            $webDriver = $magentoWebDriver->webDriver;

            $magentoWebDriver->waitForPageLoad(30);
            $storeViews = $webDriver->findElements(WebDriverBy::xpath($storeViewsToDelete));
            while (!empty($storeViews)) {
                $storeViews[0]->click();
                $magentoWebDriver->waitForPageLoad(30);
                $magentoWebDriver->waitForElementVisible($deleteButton, 10);
                $magentoWebDriver->click($deleteButton);
                $magentoWebDriver->waitForPageLoad(30);
                $magentoWebDriver->waitForElementVisible($createDbBackupButton, 10);
                $magentoWebDriver->selectOption($createDbBackupButton, 'No');
                $magentoWebDriver->click($deleteButton);
                $magentoWebDriver->waitForPageLoad(60);
                $magentoWebDriver->waitForText($successMessage, 10, $successMessageContainer);
                $storeViews = $webDriver->findElements(WebDriverBy::xpath($storeViewsToDelete));
            }
        } catch (\Exception $exception) {
            $this->fail($exception->getMessage());
        }
    }
}
