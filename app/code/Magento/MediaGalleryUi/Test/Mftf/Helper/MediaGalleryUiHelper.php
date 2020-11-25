<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryUi\Test\Mftf\Helper;

use Facebook\WebDriver\Remote\RemoteWebDriver as FacebookWebDriver;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\WebDriverBy;
use Magento\FunctionalTestingFramework\Helper\Helper;
use Magento\FunctionalTestingFramework\Module\MagentoWebDriver;

/**
 * Class for MFTF helpers for MediaGalleryUi module.
 */
class MediaGalleryUiHelper extends Helper
{
    /**
     * Delete all images using mass action.
     *
     * @param string $emptyRow
     * @param string $deleteImagesButton
     * @param string $checkImage
     * @param string $deleteSelectedButton
     * @param string $modalAcceptButton
     * @param string $successMessageContainer
     * @param string $successMessage
     *
     * @return void
     */
    public function deleteAllImagesUsingMassAction(
        string $emptyRow,
        string $deleteImagesButton,
        string $checkImage,
        string $deleteSelectedButton,
        string $modalAcceptButton,
        string $successMessageContainer,
        string $successMessage
    ): void {
        try {
            /** @var MagentoWebDriver $webDriver */
            $magentoWebDriver = $this->getModule('\Magento\FunctionalTestingFramework\Module\MagentoWebDriver');
            /** @var FacebookWebDriver $webDriver */
            $webDriver = $magentoWebDriver->webDriver;
            $rows = $webDriver->findElements(WebDriverBy::cssSelector($emptyRow));
            while (empty($rows)) {
                $magentoWebDriver->click($deleteImagesButton);
                $magentoWebDriver->waitForPageLoad(30);
                $magentoWebDriver->waitForElementVisible($deleteSelectedButton, 10);

                // Check all images
                /** @var RemoteWebElement[] $images */
                $imagesCheckboxes = $webDriver->findElements(WebDriverBy::cssSelector($checkImage));
                /** @var RemoteWebElement $image */
                foreach ($imagesCheckboxes as $imageCheckbox) {
                    $imageCheckbox->click();
                }

                $magentoWebDriver->waitForPageLoad(30);
                $magentoWebDriver->click($deleteSelectedButton);
                $magentoWebDriver->waitForPageLoad(30);
                $magentoWebDriver->waitForElementVisible($modalAcceptButton, 10);
                $magentoWebDriver->click($modalAcceptButton);
                $magentoWebDriver->waitForPageLoad(60);
                $magentoWebDriver->waitForElementVisible($successMessageContainer, 10);
                $magentoWebDriver->see($successMessage, $successMessageContainer);

                $rows = $webDriver->findElements(WebDriverBy::cssSelector($emptyRow));
            }
        } catch (\Exception $e) {
            $this->fail($e->getMessage());
        }
    }
}
