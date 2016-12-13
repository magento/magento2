<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Mtf\Client\Element;

use Magento\Mtf\Client\Locator;

/**
 * General class for suggest elements.
 */
class SuggestElement extends SimpleElement
{
    /**
     * "Backspace" key code.
     */
    const BACKSPACE = "\xEE\x80\x83";

    /**
     * Selector for advanced select element.
     *
     * @var string
     */
    protected $advancedSelect = '[data-role="advanced-select"]';

    /**
     * Selector for select input element.
     *
     * @var string
     */
    protected $selectInput = '[data-role="advanced-select-text"]';

    /**
     * Selector search result.
     *
     * @var string
     */
    protected $searchResult = '.mage-suggest-dropdown';

    /**
     * Selector item of search result.
     *
     * @var string
     */
    protected $resultItem = './/ul/li/a[text()="%s"]';

    /**
     * Search label.
     *
     * @var string
     */
    protected $searchLabel = '[data-action="advanced-select-search"]';

    /**
     * Close button.
     *
     * @var string
     */
    protected $closeButton = '[data-action="close-advanced-select"]';

    /**
     * Set value.
     *
     * @param string $value
     * @return void
     */
    public function setValue($value)
    {
        $this->eventManager->dispatchEvent(['set_value'], [__METHOD__, $this->getAbsoluteSelector()]);

        $this->clear();

        if ($value == '') {
            return;
        }
        $this->keys([$value]);
        $searchedItem = $this->find(sprintf($this->resultItem, $value), Locator::SELECTOR_XPATH);
        $this->waitUntil(
            function () use ($searchedItem) {
                return $searchedItem->isVisible();
            }
        );
        $searchedItem->click();
        $closeButton = $this->find($this->closeButton);
        if ($closeButton->isVisible()) {
            $closeButton->click();
        }
    }

    /**
     * Send keys.
     *
     * @param array $keys
     * @return void
     */
    public function keys(array $keys)
    {
        if (!$this->find($this->selectInput)->isVisible()) {
            $this->find($this->advancedSelect)->click();
        }
        $input = $this->find($this->selectInput);
        $input->click();
        $input->keys($keys);
        $this->searchResult();
    }

    /**
     * Clear value of element.
     *
     * @return void
     */
    protected function clear()
    {
        $element = $this->find($this->advancedSelect);
        while ($element->getValue() != '') {
            $element->keys([self::BACKSPACE]);
        }
    }

    /**
     * Search category result.
     *
     * @return void
     */
    public function searchResult()
    {
        $this->find($this->searchLabel)->click();
    }

    /**
     * Get value.
     *
     * @return string
     */
    public function getValue()
    {
        $this->eventManager->dispatchEvent(['get_value'], [__METHOD__, $this->getAbsoluteSelector()]);

        return $this->find($this->advancedSelect)->getValue();
    }

    /**
     * Checking exist value in search result.
     *
     * @param string $value
     * @return bool
     */
    public function isExistValueInSearchResult($value)
    {
        $needle = $this->find($this->searchResult)->find(sprintf($this->resultItem, $value), Locator::SELECTOR_XPATH);
        $keys = str_split($value);
        $this->keys($keys);
        if ($needle->isVisible()) {
            try {
                return true;
            } catch (\Exception $e) {
                // In parallel run on windows change the focus is lost on element
                // that causes disappearing of attribute suggest list.
            }
        }

        return false;
    }
}
