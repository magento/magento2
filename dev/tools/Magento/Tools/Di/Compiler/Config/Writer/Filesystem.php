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
     * @param string $key
     * @param array $config
     * @return void
     */
    public function write($key, array $config)
    {
        $this->initialize();

        $serialized = serialize($config);
        file_put_contents(BP . '/var/di/' . $key . '.ser', $serialized);
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
