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
 * Class \Magento\Setup\Module\Di\Compiler\Config\Writer\Filesystem
 *
 * @since 2.0.0
 */
class Filesystem implements WriterInterface
{
    /**
     * @var DirectoryList
     * @since 2.0.0
     */
    private $directoryList;

    /**
     * Constructor
     *
     * @param DirectoryList $directoryList
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    private function initialize()
    {
        if (!file_exists($this->directoryList->getPath(DirectoryList::GENERATED_METADATA))) {
            mkdir($this->directoryList->getPath(DirectoryList::GENERATED_METADATA));
        }
    }
}
