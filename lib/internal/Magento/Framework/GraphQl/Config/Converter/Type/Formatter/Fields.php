<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\GraphQl\Config\Converter\Type\Formatter;

use Magento\Framework\GraphQl\Config\Converter\Type\FormatterInterface;

/**
 * Format type's fields and their corresponding data parts.
 */
class Fields implements FormatterInterface
{
    /**
     * {@inheritDoc}
     *
     * Format of input entry should conform to the following structure for fields to be processed correctly:
     * ['field' => [ // Required
     *     $indexOfField => [ // At least 1 item required
     *         'name' => $nameOfField, // Required
     *         'type' => $nameOfFieldType, // Required
     *         'required => $boolean, // Optional - will default to false if not specified
     *         'itemType' => $nameOfListItemType, // Required if field is a list
     *         'resolver' => $fullyQualifiedResolverClassName, // Required only if field needs custom resolution
     *         'description' => $descriptionString, // Optional
     *         'argument' => [ // Optional
     *             'name' => $argumentName, // Required if arguments exist
     *             'type' => $argumentTypeName, // Required if arguments exist
     *             [...] // Other optional type data from argument
     *         ],
     *     ],
     *     .
     *     .
     *     .
     * ]
     *
     * Format of output entry will have the following structure:
     * ['fields => [
     *     $fieldName => [
     *         'name' => $fieldName,
     *         'type' => $typeName,
     *         'required => $isRequiredField, // Defaults to false
     *         'itemType => $nameOfListItemType, // Present only if list type,
     *         'resolver' => $fullyQualifiedResolverClassName, // Present only if custom type resolution required
     *         'description' => $descriptionString, // Present only if configured
     *         'arguments' => [ // Present only if field has configured arguments
     *             $argumentName => [
     *                 'name' => $argumentName,
     *                 'type' => $argumentTypeName,
     *                 [...] // Other optional type data from argument
     *             ],
     *         ]
     *     ],
     *     .
     *     .
     *     .
     * ]
     */
    public function format(array $entry): array
    {
        $fields = [];
        if (!empty($entry['field'])) {
            foreach ($entry['field'] as $field) {
                $fields['fields'][$field['name']] = [
                    'name' => $field['name'],
                    'type' => $field['type']
                ];
                $fields['fields'][$field['name']] = array_merge(
                    $fields['fields'][$field['name']],
                    $this->formatBoolean($field, 'required')
                );
                $fields['fields'][$field['name']] = array_merge(
                    $fields['fields'][$field['name']],
                    $this->formatString($field, 'itemType')
                );
                $fields['fields'][$field['name']] = array_merge(
                    $fields['fields'][$field['name']],
                    $this->formatString($field, 'resolver')
                );
                $fields['fields'][$field['name']] = array_merge(
                    $fields['fields'][$field['name']],
                    $this->formatString($field, 'description')
                );
                $arguments = [];
                if (isset($field['argument'])) {
                    foreach ($field['argument'] as $argument) {
                        $arguments[$argument['name']] = $argument;
                    }
                }
                $fields['fields'][$field['name']]['arguments'] = $arguments;
            }
        }

        return $fields;
    }

    /**
     * Format string if set, otherwise return empty array.
     *
     * @param array $field
     * @param string $name
     * @return array
     */
    private function formatString(array $field, string $name) : array
    {
        if (isset($field[$name])) {
            return [$name => $field[$name]];
        } else {
            return [];
        }
    }

    /**
     * Format boolean to true if set and set to 'true', otherwise set to false.
     *
     * @param array $field
     * @param string $name
     * @return array
     */
    private function formatBoolean(array $field, string $name)
    {
        if (isset($field[$name]) && $field[$name] == 'true') {
            return [$name => true];
        } else {
            return [$name => false];
        }
    }
}
