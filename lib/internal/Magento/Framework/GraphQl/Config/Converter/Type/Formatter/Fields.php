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
