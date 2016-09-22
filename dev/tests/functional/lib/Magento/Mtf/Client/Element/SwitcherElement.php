<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Mtf\Client\Element;

use Magento\Mtf\Client\Locator;

/**
 * Toggle element in the backend.
 * Switches value between YES and NO.
 */
class SwitcherElement extends SimpleElement
{
    /**
     * XPath locator of the parent container.
     *
     * @var string
     */
    protected $parentContainer = 'parent::div[@data-role="switcher"]';

    /**
     * XPath selector for label text on swticher element.
     *
     * @var string
     */
    private $labelText = './following-sibling::label';

    /**
     * Set value to Yes or No.
     *
     * @param string $value Yes|No
     * @return void
     */
    public function setValue($value)
    {
        if (($value != 'Yes') && ($value != 'No')) {
            throw new \UnexpectedValueException(
                sprintf('Switcher element accepts only "Yes" and "No" values.')
            );
        }
        if ($value != $this->getValue()) {
            $this->find($this->labelText, Locator::SELECTOR_XPATH)->click();
        }
    }

    /**
     * Get the current value.
     *
     * @return string 'Yes'|'No'
     * @throws \Exception
     */
    public function getValue()
    {
        if ($this->find($this->parentContainer, 'xpath')->find('input:checked')->isVisible()) {
            return 'Yes';
        } elseif ($this->find($this->parentContainer, 'xpath')->find('input')->isVisible()) {
            return 'No';
        } else {
            throw new \Exception(
                sprintf('Element %s not found on page', $this->getLocator())
            );
        }
    }
}
