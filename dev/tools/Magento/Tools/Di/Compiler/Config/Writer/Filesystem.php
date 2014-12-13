<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Tools\Di\Compiler\Config\Writer;

use Magento\Tools\Di\Compiler\Config\WriterInterface;

class Filesystem implements WriterInterface
{
    /**
     * Writes config in storage
     *
     * @param string $areaCode
     * @param array $config
     * @return void
     */
    public function write($areaCode, array $config)
    {
        $this->initialize();
        foreach ($config['arguments'] as $key => $value) {
            if ($value !== null) {
                $config['arguments'][$key] = serialize($value);
            }
        }

        $serialized = serialize($config);
        file_put_contents(BP . '/var/di/' . $areaCode . '.ser', $serialized);
    }

    /**
     * Initializes writer
     *
     * @return void
     */
    private function initialize()
    {
        if (!file_exists(BP . '/var/di')) {
            mkdir(BP . '/var/di');
        }
    }
}
