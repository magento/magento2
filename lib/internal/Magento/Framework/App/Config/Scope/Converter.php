<?php
/**
 * Configuration data converter. Converts associative array to tree array
 *
 * Copyright © Magento, Inc. All rights reserved.
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
     * @param array $container
     * @param string $path
     * @param string $value
     * @return array
     */
    protected function _setArrayValue(array $container, $path, $value)
    {
        $parts = explode('/', $path);
        if (count($parts) > 0) {
            $parts = array_reverse($parts);
            $result = $value;
            foreach ($parts as $part) {
                $part = trim($part);
                if ($part !== '') {
                    $result = [$part => $result];
                }
            }
            $container = array_merge_recursive($container, $result);
        }
        return $container;
    }
}
