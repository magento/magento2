<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Setup;

use Magento\Framework\DB\DataConverter\DataConversionException;
use Magento\Framework\DB\DataConverter\SerializedToJson;

/**
 * Convert conditions_encoded part of cms block content data from serialized to JSON format
 */
class BlockContentConverter extends SerializedToJson
{
    /**
     * Convert conditions_encoded part of block content from serialized to JSON format
     *
     * @param string $value
     * @return string
     * @throws DataConversionException
     */
    public function convert($value)
    {
        preg_match_all('/(.*?){{(widget)(.*?)}}(.*?)/si', $value, $matches, PREG_SET_ORDER);
        $segments = $matches[0];
        $tokenizer = new \Magento\Framework\Filter\Template\Tokenizer\Parameter();
        $tokenizer->setString($segments[3]);
        $widgetParameters = $tokenizer->tokenize();
        if (isset($widgetParameters['conditions_encoded'])) {
            if ($this->isConvertedConditions($widgetParameters['conditions_encoded'])) {
                return $value;
            }
            // Replace special characters in serialized conditon from database
            $conditions = str_replace(['[', ']', '`', '|'], ['{', '}', '"', '\\'], $widgetParameters['conditions_encoded']);
            $conditions = parent::convert($conditions);
            // Replace special characters in json encoded conditon for storing in database
            $widgetParameters['conditions_encoded'] = str_replace(
                ['{', '}', '"', '\\\\'], ['[', ']', '`', '|'],
                $conditions
            );
            $segments[3] = '';
            foreach ($widgetParameters as $key => $parameter) {
                $segments[3] = $segments[3] . ' ' . $key . '="' . $parameter . '"';
            }
            return $segments[1] . '{{' . $segments[2] . $segments[3] . '}}' . $segments[4];
        }

        return $value;
    }

    /**
     * Check if conditions have been converted to JSON
     *
     * @param string $value
     * @return bool
     */
    private function isConvertedConditions($value)
    {
        // Replace special characters in json encoded condition from database
        $value = str_replace(['[', ']', '`', '|'], ['{', '}', '"', '\\\\'], $value);
        return $this->isValidJsonValue($value);
    }
}
