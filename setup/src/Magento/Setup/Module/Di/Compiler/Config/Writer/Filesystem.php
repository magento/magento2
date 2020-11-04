<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Module\Di\Compiler\Config\Writer;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Setup\Module\Di\Compiler\Config\WriterInterface;

/**
 * Class for writing DI Compiler Configuration
 *
 * @deprecated Moved to Framework to allow broader reuse
 * @see \Magento\Framework\App\ObjectManager\ConfigWriter\Filesystem
 */
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
        $configuration = sprintf('<?php return %s;', var_export($config, true));
        file_put_contents(
            $this->directoryList->getPath(DirectoryList::GENERATED_METADATA) . '/' . $key  . '.php',
            $configuration
        );
    }

    /**
     * Initializes writer
     *
     * @return void
     */
    private function initialize()
    {
        if (!file_exists($this->directoryList->getPath(DirectoryList::GENERATED_METADATA))) {
            mkdir($this->directoryList->getPath(DirectoryList::GENERATED_METADATA));
        }
    }
}
