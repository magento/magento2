<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Handler;

use Magento\Mtf\Handler\Curl;

/**
 * Class Conditions
 * Curl class for fixture with conditions
 *
 * Format value of conditions.
 * Add slash to symbols: "{", "}", "[", "]", ":".
 * 1. Single condition:
 * [Type|Param|Param|...|Param]
 * 2. List conditions:
 * [Type|Param|Param|...|Param]
 * [Type|Param|Param|...|Param]
 * [Type|Param|Param|...|Param]
 * 3. Combination condition with single condition
 * {Type|Param|Param|...|Param:[Type|Param|Param|...|Param]}
 * 4. Combination condition with list conditions
 * {Type|Param|Param|...|Param:[[Type|Param|...|Param][Type|Param|...|Param]...[Type|Param|...|Param]]}
 *
 * Example value:
 * {Products subselection|total amount|greater than|135|ANY:[[Price in cart|is|100][Quantity in cart|is|100]]}
 * {Conditions combination:[
 *     [Subtotal|is|100]
 *     {Product attribute combination|NOT FOUND|ANY:[[Attribute Set|is|Default][Attribute Set|is|Default]]}
 * ]}
 */
abstract class Conditions extends Curl
{
    /**
     * Map of type parameter
     *
     * @var array
     */
    protected $mapTypeParams = [];

    /**
     * Map of rule parameters
     *
     * @var array
     */
    protected $mapRuleParams = [
        'operator' => [
            'is' => '==',
            'is not' => '!=',
            'equal to' => '==',
            'matches' => '==',
        ],
        'value_type' => [
            'same_as' => 'the Same as Matched Product Categories',
        ],
        'value' => [
            'California' => '12',
            'United States' => 'US',
            '[flatrate] Fixed' => 'flatrate_flatrate',
        ],
        'aggregator' => [
            'ALL' => 'all',
        ],
    ];

    /**
     * Map encode special chars
     *
     * @var array
     */
    protected $encodeChars = [
        '\{' => '&lbrace;',
        '\}' => '&rbrace;',
        '\[' => '&lbracket;',
        '\]' => '&rbracket;',
        '\:' => '&colon;',
    ];

    /**
     * Map decode special chars
     *
     * @var array
     */
    protected $decodeChars = [
        '&lbrace;' => '{',
        '&rbrace;' => '}',
        '&lbracket;' => '[',
        '&rbracket;' => ']',
        '&colon;' => ':',
    ];

    /**
     * Prepare conditions to array for send by post request
     *
     * @param string $conditions
     * @return array
     */
    protected function prepareCondition($conditions)
    {
        $decodeConditions = empty($conditions)
            ? $this->decodeValue("[Conditions combination]")
            : $this->decodeValue("{Conditions combination:[{$conditions}]}");
        return $this->convertMultipleCondition($decodeConditions);
    }

    /**
     * Convert condition combination
     *
     * @param string $combination
     * @param array|string $conditions
     * @param int $nesting
     * @return array
     */
    private function convertConditionsCombination($combination, $conditions, $nesting)
    {
        $combination = [$nesting => $this->convertSingleCondition($combination)];
        $conditions = $this->convertMultipleCondition($conditions, $nesting, 1);
        return $combination + $conditions;
    }

    /**
     * Convert multiple condition
     *
     * @param array $conditions
     * @param int $nesting
     * @param int $count
     * @return array
     */
    private function convertMultipleCondition(array $conditions, $nesting = 1, $count = 0)
    {
        $result = [];
        foreach ($conditions as $key => $condition) {
            $curNesting = $nesting . ($count ? ('--' . $count) : '');

            if (!is_numeric($key)) {
                $result += $this->convertConditionsCombination($key, $condition, $curNesting);
            } elseif (is_string($condition)) {
                $result[$curNesting] = $this->convertSingleCondition($condition);
            } else {
                $result += $this->convertMultipleCondition($condition, $nesting, $count);
            }
            $count++;
        }
        return $result;
    }

    /**
     * Convert single condition
     *
     * @param string $condition
     * @return array
     * @throws \Exception
     */
    private function convertSingleCondition($condition)
    {
        $condition = $this->parseCondition($condition);
        extract($condition);

        $typeParam = $this->getTypeParam($type);
        if (empty($typeParam)) {
            throw new \Exception("Can't find type param \"{$type}\".");
        }

        $ruleParam = [];
        foreach ($rules as $value) {
            $param = $this->getRuleParam($value);
            if (empty($param)) {
                $ruleParam['value'] = $value;
                break;
            }
            $ruleParam += $param;
        }
        if (count($ruleParam) != count($rules)) {
            throw new \Exception(
                "Can't find all params. "
                . "\nSearch: " . implode(', ', $rules) . " "
                . "\nFind: " . implode(', ', $ruleParam)
            );
        }

        return $typeParam + $ruleParam;
    }

    /**
     * Get type param by name
     *
     * @param string $name
     * @return array
     */
    private function getTypeParam($name)
    {
        return isset($this->mapTypeParams[$name]) ? $this->mapTypeParams[$name] : [];
    }

    /**
     * Get rule param by name
     *
     * @param string $name
     * @return array
     */
    private function getRuleParam($name)
    {
        foreach ($this->mapRuleParams as $typeParam => &$params) {
            if (isset($params[$name])) {
                return [$typeParam => $params[$name]];
            }
        }
        return [];
    }

    /**
     * Decode value
     *
     * @param string $value
     * @return array
     * @throws \Exception
     */
    private function decodeValue($value)
    {
        $value = str_replace(array_keys($this->encodeChars), $this->encodeChars, $value);
        $value = preg_replace('/(\]|})({|\[)/', '$1,$2', $value);
        $value = preg_replace('/{([^:]+):/', '{"$1":', $value);
        $value = preg_replace('/\[([^\[{])/', '"$1', $value);
        $value = preg_replace('/([^\]}])\]/', '$1"', $value);
        $value = str_replace(array_keys($this->decodeChars), $this->decodeChars, $value);

        $value = "[{$value}]";
        $value = json_decode($value, true);
        if (null === $value) {
            throw new \Exception('Bad format value.');
        }
        return $value;
    }

    /**
     * Parse condition
     *
     * @param string $condition
     * @return array
     * @throws \Exception
     */
    private function parseCondition($condition)
    {
        if (!preg_match_all('/([^|]+\|?)/', $condition, $match)) {
            throw new \Exception('Bad format condition');
        }
        foreach ($match[1] as $key => $value) {
            $match[1][$key] = rtrim($value, '|');
        }

        return [
            'type' => array_shift($match[1]),
            'rules' => array_values($match[1]),
        ];
    }
}
