<?php
/**
 * Configuration data converter. Converts associative array to tree array
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Config\Scope;

class Converter implements \Magento\Framework\Config\ConverterInterface
{
    /**
     * Convert config data
     *
     * @param array $source
     * @return array
     */
    public function convert($source)
    {
        $output = [];
        foreach ($source as $key => $value) {
            $output = $this->_setArrayValue($output, $key, $value);
        }
        return $output;
    }

    /**
     * Set array value by path
     *
     * @param array $output
     * @param string $path
     * @param string $value
     * @return array
     */
    protected function _setArrayValue(array $output, $path, $value)
    {
        $parts = array_reverse(explode('/', $path));

        $result = $value;
        foreach ($parts as $part) {
            $result = [$part => $result];
        }
        return array_merge_recursive($output, $result);
    }
}
