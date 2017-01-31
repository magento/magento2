<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Upgrade\Test\Block;

use Magento\Mtf\Block\Form;
use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Client\ElementInterface;

/**
 * Select version block.
 */
class SelectVersion extends Form
{
    /**
     * 'Next' button.
     *
     * @var string
     */
    protected $next = "[ng-click*='update']";

    /**
     * First field selector
     *
     * @var string
     */
    protected $firstField = '#selectVersion';

    /**
     * Other components loader selector
     *
     * @var string
     */
    private $loader = 'div[ng-show="updateComponents.yes && !upgradeProcessError"] > div.message.message-spinner';

    /**
     * Click on 'Next' button.
     *
     * @return void
     */
    public function clickNext()
    {
        $this->_rootElement->find($this->next, Locator::SELECTOR_CSS)->click();
    }

    /**
     * Ensure the form is loaded and fill the root form
     *
     * @param FixtureInterface $fixture
     * @param SimpleElement|null $element
     * @return $this
     */
    public function fill(FixtureInterface $fixture, SimpleElement $element = null)
    {
        $this->waitForElementVisible($this->firstField);
        return parent::fill($fixture, $element);
    }

    /**
     * Choose 'yes' for upgrade option called 'Other components'
     *
     * @return void
     */
    public function chooseUpgradeOtherComponents()
    {
        $this->_rootElement->find("[for=yesUpdateComponents]", Locator::SELECTOR_CSS)->click();
        $this->waitForElementVisible($this->loader);
        $this->waitForElementNotVisible($this->loader);
    }

    /**
     * Set maximum compatible sample data for each row
     * Iterates through each page of the grid and sets the compatible version from fixture
     *
     * @param string $sampleDataVersion
     * @return void
     */
    public function chooseVersionUpgradeOtherComponents($sampleDataVersion)
    {
        do {
            $this->iterateAndSetComponentsRows($this->convertVersionFixtureToRegexp($sampleDataVersion));
        } while ($this->canClickOnNextPage());
    }

    /**
     * Gets components rows as ElementInterface
     *
     * @return ElementInterface[]
     */
    private function getComponentsTableRows()
    {
        return $this->_rootElement->getElements("table.data-grid tbody tr");
    }

    /**
     * Iterate through components in table and set compatible version for selected magento version
     *
     * @param $sampleDataVersion
     * @return void
     */
    private function iterateAndSetComponentsRows($sampleDataVersion)
    {
        $rows = $this->getComponentsTableRows();
        for ($rowIndex = 1; $rowIndex <= count($rows); $rowIndex++) {
            $textElement = $this->getRowComponentTitle($rowIndex);
            if ($this->titleContainsSampleData($textElement)) {
                $this->setSampleDataVersionToRowSelect($rowIndex, $sampleDataVersion);
            }
        }
    }

    /**
     * Clicks on Next Page of the grid
     *
     * @return void
     */
    private function clickOnNextPage()
    {
        $this->_rootElement->find(".admin__data-grid-pager .action-next", Locator::SELECTOR_CSS)->click();
    }

    /**
     * Can click on next page
     *
     * @return bool
     */
    private function canClickOnNextPage()
    {
        $element = $this->_rootElement->find(".admin__data-grid-pager .action-next");
        if ($element->isVisible()) {
            $result = !$element->isDisabled();
            $this->clickOnNextPage();
            return $result;
        }
        return false;
    }

    /**
     * Gets rows component title
     *
     * @param int $rowIndex
     * @return ElementInterface
     */
    private function getRowComponentTitle($rowIndex)
    {
        return $this->_rootElement->find(
            "//table//tbody//tr[" . $rowIndex . "]//td//*[contains(text(),'sample')]",
            Locator::SELECTOR_XPATH
        );
    }

    /**
     * Gets the select element from row
     *
     * @param int $rowIndex
     * @return ElementInterface
     */
    private function getSelectFromRow($rowIndex)
    {
        return $this->_rootElement->find(
            '//table//tbody//tr[' . $rowIndex . ']//td//select',
            Locator::SELECTOR_XPATH,
            'select'
        );
    }

    /**
     * Convert sample data version fixture to regexp format
     * Example 100.1.* to 100\.1\.[0-9]+
     *
     * @param string $sampleDataVersion
     * @return string
     * @throws \Exception
     */
    private function convertVersionFixtureToRegexp($sampleDataVersion)
    {
        if (!preg_match('/\d+(?:\.*\d*)*/', $sampleDataVersion)) {
            throw new \Exception('Wrong format for sample data version fixture. Example: 100.1.* needed.');
        }
        return str_replace('*', '[0-9]+', $sampleDataVersion);
    }

    /**
     * Asserts if element's text contains sample data
     *
     * @param ElementInterface $element
     * @return bool
     */
    private function titleContainsSampleData($element)
    {
        return preg_match('/magento\/.*sample-data/', $element->getText());
    }

    /**
     * Sets sample data version matching the maximum compatible version from fixture
     *
     * @param int $rowIndex
     * @param string $sampleDataVersionForRegex
     * @return void
     */
    private function setSampleDataVersionToRowSelect($rowIndex, $sampleDataVersionForRegex)
    {
        $selectElement = $this->getSelectFromRow($rowIndex);
        $optionTextArray = [];
        foreach ($selectElement->getElements('option') as $option) {
            $optionText = $option->getText();
            if (preg_match('/' . $sampleDataVersionForRegex . '/', $optionText)) {
                preg_match('/([0-9\.\-a-zA-Z]+)/', $optionText, $match);
                $optionTextArray[$optionText] = current($match);
            }
        }

        if (!empty($optionTextArray)) {
            uasort(
                $optionTextArray,
                function ($versionOne, $versionTwo) {
                    return version_compare($versionOne, $versionTwo) * -1;
                }
            );

            $toSelectVersion = key($optionTextArray);
            if ($toSelectVersion !== $selectElement->getText()) {
                $selectElement->setValue($toSelectVersion);
            }
        }
    }
}
