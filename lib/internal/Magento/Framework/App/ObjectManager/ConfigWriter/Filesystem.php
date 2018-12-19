<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\App\ObjectManager\ConfigWriter;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager\ConfigWriterInterface;

/**
 * @inheritdoc
 */
class Filesystem implements ConfigWriterInterface
{
    /**
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * @param DirectoryList $directoryList
     */
    public function __construct(
        DirectoryList $directoryList
    ) {
        $this->directoryList = $directoryList;
    }

    /**
     * Writes config in storage
     *
     * @param string $key
     * @param array $config
     * @return void
     */
    public function write(string $key, array $config)
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
