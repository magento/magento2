<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */

/**
 * Adminhtml JavaScript helper
 */
namespace Magento\Backend\Helper;

/**
 * @api
 * @since 100.0.2
 */
class Js
{
    /**
     * Decode serialized grid data
     *
     * Ignores non-numeric array keys
     *
     * '1&2&3&4' will be decoded into:
     * array(1, 2, 3, 4);
     *
     * otherwise the following format is anticipated:
     * 1=<encoded string>&2=<encoded string>:
     * array (
     *   1 => array(...),
     *   2 => array(...),
     * )
     *
     * @param   string $encoded
     * @return  array
     */
    public function decodeGridSerializedInput($encoded)
    {
        $isSimplified = false === strpos($encoded, '=');
        $result = [];
        // phpcs:ignore Magento2.Functions.DiscouragedFunction.Discouraged
        parse_str($encoded, $decoded);
        foreach ($decoded as $key => $value) {
            if (is_numeric($key)) {
                if ($isSimplified) {
                    $result[] = $key;
                } else {
                    $result[$key] = null;
                    // phpcs:ignore Magento2.Functions.DiscouragedFunction.Discouraged
                    parse_str(base64_decode($value), $result[$key]);
                }
            }
        }
        return $result;
    }
}
