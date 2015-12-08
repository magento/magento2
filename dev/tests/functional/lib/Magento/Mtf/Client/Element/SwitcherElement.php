<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Mtf\Client\Element;

use Magento\Mtf\ObjectManager;

/**
 * Toggle element in the backend.
 * Switches value between YES and NO
 */
class SwitcherElement extends SimpleElement
{
    /**
     * Set value to conditions.
     *
     * @param string $value
     * @return void
     */
    public function setValue($value)
    {
        if (( $value != 'Yes' ) && ( $value != 'No' ))
        {
            throw new \UnexpectedValueException(
                sprintf('Switcher element accepts only "Yes" and "No" values.')
            );
        }
        if ($value != $this->getValue())
        {
            $this->click();
        }
    }

    /**
     * Get value from conditions.
     *
     * @return null
     */
    public function getValue()
    {
        if ($this->find('input:checked')->isVisible())
        {
            return 'Yes';
        }
        else
        {
            return 'No';
        }
    }
}
