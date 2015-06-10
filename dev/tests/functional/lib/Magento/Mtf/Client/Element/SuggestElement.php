<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
     * Selector suggest input.
     *
     * @var string
     */
    protected $suggest = '.mage-suggest-inner > .search';

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
     * Suggest state loader.
     *
     * @var string
     */
    protected $suggestStateLoader = '.mage-suggest-state-loading';

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
        foreach (str_split($value) as $symbol) {
            $this->keys([$symbol]);
            $searchedItem = $this->find(sprintf($this->resultItem, $value), Locator::SELECTOR_XPATH);
            if ($searchedItem->isVisible()) {
                try {
                    $searchedItem->click();
                    break;
                } catch (\Exception $e) {
                    // In parallel run on windows change the focus is lost on element
                    // that causes disappearing of category suggest list.
                }
            }
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
        $input = $this->find($this->suggest);
        $input->click();
        $input->keys($keys);
        $this->waitResult();
    }

    /**
     * Clear value of element.
     *
     * @return void
     */
    protected function clear()
    {
        $element = $this->find($this->suggest);
        while ($element->getValue() != '') {
            $element->keys([self::BACKSPACE]);
        }
    }

    /**
     * Wait for search result is visible.
     *
     * @return void
     */
    public function waitResult()
    {
        $browser = $this;
        $selector = $this->suggestStateLoader;
        $browser->waitUntil(
            function () use ($browser, $selector) {
                $element = $browser->find($selector);
                return $element->isVisible() == false ? true : null;
            }
        );
    }

    /**
     * Get value.
     *
     * @return string
     */
    public function getValue()
    {
        $this->eventManager->dispatchEvent(['get_value'], [__METHOD__, $this->getAbsoluteSelector()]);

        return $this->find($this->suggest)->getValue();
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
