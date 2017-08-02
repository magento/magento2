<?php
/**
 * Configuration data converter. Converts associative array to tree array
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Config\Scope;

/**
 * Class \Magento\Framework\App\Config\Scope\Converter
 *
 * @since 2.0.0
 */
class Converter implements \Magento\Framework\Config\ConverterInterface
{
    /**
     * Convert config data
     *
     * @param array $source
     * @return array
     * @since 2.0.0
     */
    public function convert($source)
    {
        $output = [];
        foreach ($source as $key => $value) {
            $this->_setArrayValue($output, $key, $value);
        }
        return $output;
    }

    /**
     * Set array value by path
     *
     * @param array &$container
     * @param string $path
     * @param string $value
     * @return void
     * @since 2.0.0
     */
    protected function _setArrayValue(array &$container, $path, $value)
    {
        $segments = explode('/', $path);
        $currentPointer = & $container;
        foreach ($segments as $segment) {
            if (!isset($currentPointer[$segment])) {
                $currentPointer[$segment] = [];
            }
            $currentPointer = & $currentPointer[$segment];
        }
        $currentPointer = $value;
    }
}
