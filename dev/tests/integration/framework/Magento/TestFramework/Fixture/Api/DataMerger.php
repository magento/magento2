<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Fixture\Api;

use Magento\Framework\Api\AttributeInterface;
use Magento\Framework\Api\CustomAttributesDataInterface;
use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Data merger for api payload
 */
class DataMerger
{
    /**
     * Merge default api data with custom data
     *
     * @param array $defaultData
     * @param array $data
     * @param bool $isExtensible
     * @return array
     */
    public function merge(array $defaultData, array $data, bool $isExtensible = true): array
    {
        if ($isExtensible) {
            if (isset($data[CustomAttributesDataInterface::CUSTOM_ATTRIBUTES])) {
                $data[CustomAttributesDataInterface::CUSTOM_ATTRIBUTES] = $this->convertCustomAttributesToMap(
                    $data[CustomAttributesDataInterface::CUSTOM_ATTRIBUTES]
                );
            }
            foreach ($data as $key => $value) {
                if (!array_key_exists($key, $defaultData)) {
                    if (array_key_exists($key, $defaultData[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY])) {
                        $data[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY][$key] = $value;
                    } else {
                        $data[CustomAttributesDataInterface::CUSTOM_ATTRIBUTES][$key] = $value;
                    }
                    unset($data[$key]);
                }
            }
        }

        $result = $this->mergeRecursive($defaultData, $data);

        if ($isExtensible && isset($result[CustomAttributesDataInterface::CUSTOM_ATTRIBUTES])) {
            $result[CustomAttributesDataInterface::CUSTOM_ATTRIBUTES] = $this->convertCustomAttributesToCollection(
                $result[CustomAttributesDataInterface::CUSTOM_ATTRIBUTES]
            );
        }

        return $result;
    }

    /**
     * Recursively merge entity data
     *
     * @param array $arrays
     * @return array
     */
    public function mergeRecursive(array ...$arrays): array
    {
        $result = [];
        while ($arrays) {
            $array = array_shift($arrays);
            // is array an associative array
            if (array_values($array) !== $array) {
                foreach ($array as $key => $value) {
                    if (is_array($value) && array_key_exists($key, $result) && is_array($result[$key])) {
                        $result[$key] = $this->mergeRecursive($result[$key], $value);
                    } else {
                        $result[$key] = $value;
                    }
                }
            } elseif (array_values($result) === $result) {
                $result = $array;
            }
        }

        return $result;
    }

    /**
     * Return an associative array with attribute codes as key
     *
     * @param array $data
     * @return mixed
     */
    private function convertCustomAttributesToMap(array $data): array
    {
        $result = [];
        // check if data is not an associative array
        if (array_values($data) === $data) {
            foreach ($data as $item) {
                if (isset($item[AttributeInterface::VALUE])) {
                    $result[$item[AttributeInterface::ATTRIBUTE_CODE]] = $item[AttributeInterface::VALUE];
                } elseif (isset($item['selected_options'])) {
                    $result[$item[AttributeInterface::ATTRIBUTE_CODE]] = implode(
                        ',',
                        array_map(function ($option): string {
                            return $option[AttributeInterface::VALUE] ?? '';
                        }, $item['selected_options'])
                    );
                }
            }
        } else {
            $result = $data;
        }

        return $result;
    }

    /**
     * Return a multi-dimension array with attribute codes and values
     *
     * @param array $data
     * @return mixed
     */
    private function convertCustomAttributesToCollection(array $data): array
    {
        $result = [];
        foreach ($data as $key => $value) {
            $result[] = [
                AttributeInterface::ATTRIBUTE_CODE => $key,
                AttributeInterface::VALUE => $value,
            ];
        }

        return $result;
    }
}
