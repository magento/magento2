<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Module\Di\Compiler\Config\Writer;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Setup\Module\Di\Compiler\Config\WriterInterface;

class Filesystem implements WriterInterface
{
    /**
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * Constructor
     *
     * @param DirectoryList $directoryList
     */
    public function __construct(DirectoryList $directoryList)
    {
        $this->directoryList = $directoryList;
    }

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
        file_put_contents($this->directoryList->getPath(DirectoryList::DI) . '/' . $key . '.ser', $serialized);
    }

    /**
     * Initializes writer
     *
     * @return void
     */
    private function initialize()
    {
        if (!file_exists($this->directoryList->getPath(DirectoryList::DI))) {
            mkdir($this->directoryList->getPath(DirectoryList::DI), DriverInterface::WRITEABLE_DIRECTORY_MODE);
        }
    }
}
