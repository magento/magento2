<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CheckoutAgreements\Test\Mftf\Helper;

use Facebook\WebDriver\WebDriverBy;
use Magento\FunctionalTestingFramework\Helper\Helper;
use Magento\FunctionalTestingFramework\Module\MagentoWebDriver;
use Exception;

/**
 * Class for MFTF helpers for CheckoutAgreements module.
 */
class CheckoutAgreementsHelpers extends Helper
{
    /**
     * Delete all term conditions one by one from the Terms & Conditions grid page.
     *
     * @param string $rowsToDelete
     * @param string $deleteButton
     * @param string $modalAcceptButton
     * @param string $successMessage
     * @param string $successMessageContainer
     *
     * @return void
     */
    public function deleteAllTermConditionRows(
        string $rowsToDelete,
        string $deleteButton,
        string $modalAcceptButton,
        string $successMessage,
        string $successMessageContainer
    ): void {
        try {
            /** @var MagentoWebDriver $magentoWebDriver */
            $magentoWebDriver = $this->getModule("\\" . MagentoWebDriver::class);
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
        } catch (Exception $exception) {
            $this->fail($exception->getMessage());
        }
    }
}
