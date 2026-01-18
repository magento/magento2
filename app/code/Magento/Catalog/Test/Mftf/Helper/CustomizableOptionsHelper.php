<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Mftf\Helper;

use Exception;
use Magento\FunctionalTestingFramework\Helper\Helper;
use Magento\FunctionalTestingFramework\Module\MagentoWebDriver;

/**
 * Helper for adding multiple customizable options
 */
class CustomizableOptionsHelper extends Helper
{
    /**
     * Add multiple customizable option values in a loop
     *
     * @param int $start
     * @param int $end
     * @param string $titlePrefix Prefix for option titles (e.g., "OptionValue")
     * @param string $price Price for each option
     * @param string $addValueButton Selector for "Add Value" button
     * @param string $valueTitleField Selector for value title field
     * @param string $valuePriceField Selector for value price field
     * @return void
     */
    public function addMultipleCustomizableOptionValues(
        int $start,
        int $end,
        string $titlePrefix,
        string $price,
        string $addValueButton,
        string $valueTitleField,
        string $valuePriceField
    ): void {
        try {
            /** @var MagentoWebDriver $webDriver */
            $webDriver = $this->getModule('\Magento\FunctionalTestingFramework\Module\MagentoWebDriver');

            for ($i = $start; $i <= $end; $i++) {
                $webDriver->waitForElementClickable($addValueButton, 10);
                $webDriver->click($addValueButton);
                $webDriver->waitForElementVisible($valueTitleField, 10);
                $webDriver->fillField($valueTitleField, $titlePrefix . $i);
                $webDriver->fillField($valuePriceField, $price);
            }
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }
}
