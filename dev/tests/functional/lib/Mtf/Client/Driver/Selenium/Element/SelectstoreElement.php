<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Mtf\Client\Driver\Selenium\Element;

use Mtf\Client\Driver\Selenium\Element;
use Mtf\Client\Element\Locator;

/**
 * Class SelectstoreElement
 * Typified element class for option group selectors
 */
class SelectstoreElement extends SelectElement
{
    /**
     * Store option group selector
     *
     * @var string
     */
    protected $storeGroup = 'optgroup[option[contains(.,"%s")]]';

    /**
     * Website option group selector
     *
     * @var string
     */
    protected $website = 'optgroup[following-sibling::optgroup[option[contains(.,"%s")]]]';

    /**
     * Get the value of form element
     *
     * @return string
     */
    public function getValue()
    {
        $selectedLabel = trim($this->_getWrappedElement()->selectedLabel());
        $value = trim(
            $this->_getWrappedElement()->byXPath(sprintf($this->website, $selectedLabel))->attribute('label')
        );
        $value .= '/' . trim(
            $this->_getWrappedElement()->byXPath(sprintf($this->storeGroup, $selectedLabel))->attribute('label'),
            chr(0xC2) . chr(0xA0)
        );
        $value .= '/' . $selectedLabel;
        return $value;
    }

    /**
     * Select value in dropdown which has option groups
     *
     * @param string $value
     * @throws \Exception
     * @return void
     */
    public function setValue($value)
    {
        $group = explode('/', $value);
        $optionLocator = './/optgroup[contains(@label,"'
            . $group[0] . '")]/following-sibling::optgroup[contains(@label,"'
            . $group[1] . '")]/option[contains(text(), "'
            . $group[2] . '")]';
        $option = $this->_context->find($optionLocator, Locator::SELECTOR_XPATH);
        if (!$option->isVisible()) {
            throw new \Exception('[' . implode('/', $value) . '] option is not visible in store switcher.');
        }
        $option->click();
    }
}
