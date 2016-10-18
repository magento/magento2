<?php
/**
 * Provide access to data. Each Source can be responsible for each storage, where config data can be placed
 *
 * @copyright {}
 */
namespace Magento\Framework\App\Config;

/**
 * Interface ConfigSourceInterface
 */
interface ConfigSourceInterface
{
    /**
     * Retrieve configuration raw data array.
     *
     * @param string $path
     * @return array
     */
    public function get($path = '');
}
