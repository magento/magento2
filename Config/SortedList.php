<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Config;

use Magento\Framework\Phrase;

/**
 * Sort objects.
 */
trait SortedList
{
    /**
     * Sort array by sortAttribute value.
     *
     * @param array $array
     * @param string $instanceType
     * @param string $key
     * @return array
     */
    function sort($array, $instanceType, $key)
    {
        $output = [];
        uasort(
            $array,
            function ($firstItem, $secondItem) {
                $firstValue = 0;
                $secondValue = 0;
                if (isset($firstItem['sortOrder'])) {
                    $firstValue = intval($firstItem['sortOrder']);
                }

                if (isset($secondItem['sortOrder'])) {
                    $secondValue = intval($secondItem['sortOrder']);
                }

                if ($firstValue == $secondValue) {
                    return 0;
                }
                return $firstValue < $secondValue ? -1 : 1;
            }
        );
        foreach ($array as $name => $arrayItem) {
            if (!isset($arrayItem[$key]) || !($arrayItem[$key] instanceof $instanceType)) {
                throw new \InvalidArgumentException(
                    new Phrase(
                        'Object [%name] must implement %class',
                        ['name' => $name, 'class' => $instanceType]
                    )
                );
            }
            $output[$name] = $arrayItem[$key];
        }

        return $output;
    }
}
