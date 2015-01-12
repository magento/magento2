<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Mtf\Client\Driver\Selenium\Element;

use Mtf\Client\Driver\Selenium\Element;
use Mtf\Client\Element\Locator;

/**
 * Typified element class for multi suggest element.
 */
class MultisuggestElement extends SuggestElement
{
    /**
     * Selector list choice.
     *
     * @var string
     */
    protected $listChoice = './/ul[contains(@class,"mage-suggest-choices")]';

    /**
     * Selector choice item.
     *
     * @var string
     */
    protected $choice = './/li/div[text()="%s"]/..';

    /**
     * Selector choice value.
     *
     * @var string
     */
    protected $choiceValue = './/li[contains(@class,"mage-suggest-choice")]/div';

    /**
     * Selector remove choice item.
     *
     * @var string
     */
    protected $choiceClose = '.mage-suggest-choice-close';

    /**
     * Set value.
     *
     * @param array|string $values
     * @return void
     */
    public function setValue($values)
    {
        $this->_eventManager->dispatchEvent(['set_value'], [__METHOD__, $this->getAbsoluteSelector()]);

        $this->clear();
        foreach ((array)$values as $value) {
            if (!$this->isChoice($value)) {
                $this->selectWindow();
                parent::setValue($value);
            }
        }
    }

    /**
     * Get value.
     *
     * @return array
     */
    public function getValue()
    {
        $this->_eventManager->dispatchEvent(['get_value'], [(string) $this->_locator]);

        $listChoice = $this->find($this->listChoice, Locator::SELECTOR_XPATH);
        $choices = $listChoice->find($this->choiceValue, Locator::SELECTOR_XPATH)->getElements();
        $values = [];

        foreach ($choices as $choice) {
            /** @var Element $choice */
            $values[] = $choice->getText();
        }
        return $values;
    }

    /**
     * Check exist selected item.
     *
     * @param string $value
     * @return bool
     */
    protected function isChoice($value)
    {
        return $this->find(sprintf($this->choice, $value), Locator::SELECTOR_XPATH)->isVisible();
    }

    /**
     * Clear element.
     *
     * @return void
     */
    protected function clear()
    {
        $choiceClose = $this->find($this->choiceClose);
        while ($choiceClose->isVisible()) {
            $choiceClose->click();
            $choiceClose = $this->find($this->choiceClose);
        }
    }

    /**
     * Select to last window.
     *
     * @return void
     */
    protected function selectWindow()
    {
        $windowHandles = $this->_driver->windowHandles();
        $this->_driver->window(end($windowHandles));
    }
}
