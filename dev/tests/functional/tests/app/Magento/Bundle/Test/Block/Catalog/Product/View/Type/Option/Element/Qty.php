<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Test\Block\Catalog\Product\View\Type\Option\Element;

use Magento\Mtf\Client\Element\SimpleElement;

/**
 * Typified element class for qty element.
 */
class Qty extends SimpleElement
{
    /**
     * "Backspace" key code.
     */
    const BACKSPACE = "\xEE\x80\x83";

    /**
     * "RIGHT" key code.
     */
    const RIGHT = "\xEE\x80\x94";

    /**
     * Set the value.
     *
     * @param string|array $value
     * @return void
     */
    public function setValue($value)
    {
        $this->keys([self::RIGHT, self::BACKSPACE, $value]);
        $this->context->click();
    }
}
