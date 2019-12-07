<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Setup;

use Magento\Framework\Data\Wysiwyg\Normalizer;
use Magento\Framework\DB\DataConverter\DataConversionException;
use Magento\Framework\DB\DataConverter\SerializedToJson;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Serialize\Serializer\Serialize;
use Magento\Framework\Filter\Template\Tokenizer\ParameterFactory;

/**
 * Convert conditions_encoded part of cms block content data from serialized to JSON format
 */
class ContentConverter extends SerializedToJson
{
    /**
     * @var ParameterFactory
     */
    private $parameterFactory;

    /**
     * @var Normalizer
     */
    private $normalizer;

    /**
     * ContentConverter constructor
     *
     * @param Serialize $serialize
     * @param Json $json
     * @param ParameterFactory $parameterFactory
     * @param Normalizer $normalizer
     */
    public function __construct(
        Serialize $serialize,
        Json $json,
        ParameterFactory $parameterFactory,
        Normalizer $normalizer
    ) {
        $this->parameterFactory = $parameterFactory;
        $this->normalizer = $normalizer;
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
        preg_match_all('/(.*?){{widget(.*?)}}/si', $value, $matches, PREG_SET_ORDER);
        if (empty($matches)) {
            return $value;
        }
        $convertedValue = '';
        foreach ($matches as $match) {
            $convertedValue .= $match[1] . '{{widget' . $this->convertWidgetParams($match[2]) . '}}';
        }
        preg_match_all('/(.*?{{widget.*?}})*(?<ending>.*?)$/si', $value, $matchesTwo, PREG_SET_ORDER);
        if (isset($matchesTwo[0])) {
            $convertedValue .= $matchesTwo[0]['ending'];
        }

        return $convertedValue;
    }

    /**
     * @inheritdoc
     */
    protected function isValidJsonValue($value)
    {
        return parent::isValidJsonValue($this->normalizer->restoreReservedCharacters($value));
    }

    /**
     * Convert widget parameters from serialized format to JSON format
     *
     * @param string $paramsString
     * @return string
     */
    private function convertWidgetParams($paramsString)
    {
        /** @var \Magento\Framework\Filter\Template\Tokenizer\Parameter $tokenizer */
        $tokenizer = $this->parameterFactory->create();
        $tokenizer->setString($paramsString);
        $widgetParameters = $tokenizer->tokenize();
        if (isset($widgetParameters['conditions_encoded'])) {
            if ($this->isValidJsonValue($widgetParameters['conditions_encoded'])) {
                return $paramsString;
            }
            $conditions = $this->restoreReservedCharactersInSerializedContent($widgetParameters['conditions_encoded']);
            $widgetParameters['conditions_encoded'] = $this->normalizer->replaceReservedCharacters(
                parent::convert($conditions)
            );
            $newParamsString = '';
            foreach ($widgetParameters as $key => $parameter) {
                $newParamsString .= ' ' . $key . '="' . $parameter . '"';
            }
            return $newParamsString;
        } else {
            return $paramsString;
        }
    }

    /**
     * Restore the reserved characters in the existing serialized content
     *
     * @param string $serializedContent
     * @return string
     */
    private function restoreReservedCharactersInSerializedContent($serializedContent)
    {
        $map = [
            '{' => '[',
            '}' => ']',
            '"' => '`',
            '\\' => '|',
        ];
        return str_replace(
            array_values($map),
            array_keys($map),
            $serializedContent
        );
    }
}
