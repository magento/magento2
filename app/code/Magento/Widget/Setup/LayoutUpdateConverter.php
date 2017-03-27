<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Widget\Setup;

use Magento\Framework\DB\DataConverter\DataConversionException;
use Magento\Framework\DB\DataConverter\SerializedToJson;
use Magento\Widget\Helper\Conditions;

/**
 * Convert conditions_encoded part of layout update data from serialized to JSON format
 */
class LayoutUpdateConverter extends SerializedToJson
{
    /**
     * Convert conditions_encoded part of layout update data from serialized to JSON format
     *
     * @param string $value
     * @return string
     * @throws DataConversionException
     */
    public function convert($value)
    {
        preg_match_all(
            '/^(.*?conditions_encoded<\/argument><argument [^<]*>)(.*?)(<\/argument>.*?)$/si',
            $value,
            $matches,
            PREG_SET_ORDER
        );
        if (isset($matches[0])) {
            return $this->convertMatchString($matches[0]);
        } else {
            return $value;
        }
    }

    /**
     * Convert matching string
     *
     * @param array $matchSegments
     * @return string
     */
    private function convertMatchString(array $matchSegments)
    {
        // Restore WYSIWYG reserved characters in string
        $value = str_replace(
            array_values(Conditions::WYSIWYG_RESERVED_CHARCTERS_REPLACEMENT_MAP),
            array_keys(Conditions::WYSIWYG_RESERVED_CHARCTERS_REPLACEMENT_MAP),
            $matchSegments[2]
        );
        $convertedValue = parent::convert($value);
        // Replace WYSIWYG reserved characters in string
        $matchSegments[2] = str_replace(
            array_keys(Conditions::WYSIWYG_RESERVED_CHARCTERS_REPLACEMENT_MAP),
            array_values(Conditions::WYSIWYG_RESERVED_CHARCTERS_REPLACEMENT_MAP),
            $convertedValue
        );
        return $matchSegments[1] . $matchSegments[2] . $matchSegments[3];
    }
}
