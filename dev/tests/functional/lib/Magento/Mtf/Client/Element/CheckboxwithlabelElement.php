<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Mtf\Client\Element;

use Magento\Mtf\Client\Locator;

/**
 * Custom checkbox that hidden by label
 */
class CheckboxwithlabelElement extends CheckboxElement
{
    /**
     * Set checkbox value by clicking on label
     *
     * @param string $value
     */
    public function setValue($value)
    {
        $this->eventManager->dispatchEvent(['set_value'], [__METHOD__, $this->getAbsoluteSelector()]);
        if (($this->isSelected() && $value == 'No') || (!$this->isSelected() && $value == 'Yes')) {
            $this->find('./following-sibling::label', Locator::SELECTOR_XPATH)->click();
        }
    }
}
