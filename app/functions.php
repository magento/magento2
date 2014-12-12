<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

/**
 * Create value-object \Magento\Framework\Phrase
 *
 * @return string
 */
function __()
{
    $argc = func_get_args();

    $text = array_shift($argc);
    if (!empty($argc) && is_array($argc[0])) {
        $argc = $argc[0];
    }

    /**
     * Type casting to string is a workaround.
     * Many places in client code at the moment are unable to handle the \Magento\Framework\Phrase object properly.
     * The intended behavior is to use __toString(),
     * so that rendering of the phrase happens only at the last moment when needed
     */
    return (string)new \Magento\Framework\Phrase($text, $argc);
}
