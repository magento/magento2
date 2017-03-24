<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Setup;

use Magento\Framework\DB\DataConverter\DataConversionException;
use Magento\Framework\DB\DataConverter\SerializedToJson;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Serialize\Serializer\Serialize;
use Magento\Widget\Helper\Conditions;

/**
 * Convert conditions_encoded part of cms block content data from serialized to JSON format
 */
class ContentConverter extends SerializedToJson
{
    /**
     * @var \Magento\Framework\Filter\Template\Tokenizer\ParameterFactory
     */
    private $parameterFactory;

    /**
     * Constructor
     *
     * @param Serialize $serialize
     * @param Json $json
     */
    public function __construct(
        Serialize $serialize,
        Json $json,
        \Magento\Framework\Filter\Template\Tokenizer\ParameterFactory $parameterFactory
    ) {
        $this->parameterFactory = $parameterFactory;
        parent::__construct($serialize, $json);
    }

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
        $convertedValue = '';
        foreach ($matches as $match) {
            $convertedValue .= $this->convertMatchString($match);
        }
        return $convertedValue;
    }

    /**
     * Check if conditions have been converted to JSON
     *
     * @param string $value
     * @return bool
     */
    private function isConvertedConditions($value)
    {
        // Restore WYSIWYG reserved characters in string
        $value = str_replace(
            array_values(Conditions::WYSIWYG_RESERVED_CHARCTERS_REPLACEMENT_MAP),
            array_keys(Conditions::WYSIWYG_RESERVED_CHARCTERS_REPLACEMENT_MAP),
            $value
        );
        return $this->isValidJsonValue($value);
    }

    /**
     * Convert matching string
     *
     * @param array $matchSegments
     * @return string
     */
    private function convertMatchString(array $matchSegments)
    {
        /** @var \Magento\Framework\Filter\Template\Tokenizer\Parameter $tokenizer */
        $tokenizer = $this->parameterFactory->create();
        $tokenizer->setString($matchSegments[3]);
        $widgetParameters = $tokenizer->tokenize();
        if (isset($widgetParameters['conditions_encoded'])) {
            if ($this->isConvertedConditions($widgetParameters['conditions_encoded'])) {
                return $matchSegments[0];
            }
            // Restore WYSIWYG reserved characters in string
            $conditions = str_replace(
                array_values(Conditions::WYSIWYG_RESERVED_CHARCTERS_REPLACEMENT_MAP),
                array_keys(Conditions::WYSIWYG_RESERVED_CHARCTERS_REPLACEMENT_MAP),
                $widgetParameters['conditions_encoded']
            );
            $conditions = parent::convert($conditions);
            // Replace WYSIWYG reserved characters in string
            $widgetParameters['conditions_encoded'] = str_replace(
                array_keys(Conditions::WYSIWYG_RESERVED_CHARCTERS_REPLACEMENT_MAP),
                array_values(Conditions::WYSIWYG_RESERVED_CHARCTERS_REPLACEMENT_MAP),
                $conditions
            );
            $matchSegments[3] = '';
            foreach ($widgetParameters as $key => $parameter) {
                $matchSegments[3] .= ' ' . $key . '="' . $parameter . '"';
            }
            return $matchSegments[1] . '{{' . $matchSegments[2] . $matchSegments[3] . '}}' . $matchSegments[4];
        } else {
            return $matchSegments[0];
        }
    }
}
