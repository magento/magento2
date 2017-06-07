<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Mtf\Client\Element;

use Magento\Mtf\Client\Locator;

/**
 * Typified element class for option group selectors.
 */
class SimplifiedselectElement extends SelectElement
{
    /**
     * Option group locator.
     *
     * @var string
     */
    protected $optionGroupValue = ".//*[@data-title='%s' or contains(normalize-space(.), %s)]";

    /**
     * Select value in ropdown which has option groups.
     *
     * @param string $value
     * @return void
     */
    public function setValue($value)
    {
        $this->eventManager->dispatchEvent(['set_value'], [__METHOD__, $this->getAbsoluteSelector()]);
        $xpath = sprintf($this->optionGroupValue, $value, $this->escapeQuotes($value));
        $option = $this->find($xpath, Locator::SELECTOR_XPATH);
        $option->click();
    }
}
