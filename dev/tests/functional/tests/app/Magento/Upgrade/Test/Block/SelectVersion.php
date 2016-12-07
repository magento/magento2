<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Upgrade\Test\Block;

use Magento\Mtf\Block\Form;
use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Fixture\FixtureInterface;

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
        $this->waitForElementVisible("[ng-show='componentsProcessed']");
    }

    /**
     * Set maximum compatible sample data for each row
     *
     * @param string $sampleDataVersion
     * @return void
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function chooseVersionUpgradeOtherComponents($sampleDataVersion)
    {
        $perPageSelect= $this->_rootElement->find(
            "select#perPage",
            Locator::SELECTOR_CSS,
            'select'
        );
        $fixtureVersion = $sampleDataVersion;
        $fixtureVersion = '100.0.*';
        $perPageSelect->setValue(200);
        sleep(1);

        $elementsArray= $this->_rootElement->getElements("table.data-grid tbody tr");

        foreach ($elementsArray as $key => $rowElement) {
            $textElement = $this->_rootElement->find(
                "//table//tbody//tr[" . ($key + 1) . "]//td//*[contains(text(),'sample')]",
                Locator::SELECTOR_XPATH
            );
            if (preg_match('/magento.+sample.+data/', $textElement->getText())) {
                $selectElement = $this->_rootElement->find(
                    '//table//tbody//tr[' . ($key +1) . ']//td//select',
                    Locator::SELECTOR_XPATH,
                    'select'
                );

                $fixtureVersion = str_replace('*', '[0-9]+', $fixtureVersion);
                $toSelectValue = $selectElement->getValue();
                $toSelectValueOriginal = $toSelectValue;
                $toSelectVersion = '0';
                foreach ($selectElement->getElements('option') as $option) {
                    $optionText = $option->getText();
                    if (preg_match('/' . $fixtureVersion .'/', $optionText)) {
                        if (preg_match('/([0-9\.\-a-zA-Z]+)/', $optionText, $match)) {
                            if (!empty($match) > 0) {
                                if (version_compare($match[0], $toSelectVersion, '>')) {
                                    $toSelectVersion =  $match[0];
                                    $toSelectValue = $optionText;
                                }
                            }
                        }
                    }
                }

                if ($toSelectValue !== $toSelectValueOriginal) {
                    $selectElement->setValue($toSelectValue);
                }
            }
        }
    }
}
