<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Model\String;

class Service
{
    /**
     * Given a string in the form 'this_is_my_string' return a string in the form 'thisIsMyString'
     *
     * @param string $string
     * @return string
     */
    public function convertStringFromSnakeToCamelCase(string $string): string
    {
        return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $string))));
    }

    /**
     * Given an array in the form
     *
     * [
     *   'key_one' => 'one',
     *   'key_two' => 'two',
     * ]
     *
     * return a new array in the form
     *
     * [
     *   'keyOne' => 'one',
     *   'keyTwo' => 'two',
     * ]
     *
     * @param array $array
     * @return array
     */
    public function convertArrayKeysFromSnakeToCamelCase(array $array): array
    {
        $convertedArrayKeys = array_map(
            function($key) {
                return $this->convertStringFromSnakeToCamelCase($key);
            },
            array_keys($array)
        );

        return array_combine($convertedArrayKeys, array_values($array));
    }
}
