<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\DeploymentConfig;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Deployment configuration reader
 */
class Reader
{
    /**
     * Default configuration file name
     */
    const DEFAULT_FILE = 'config.php';

    /**
     * Directory list object
     *
     * @var DirectoryList
     */
    private $dirList;

    /**
     * Custom file name
     *
     * @var string
     */
    private $file;

    /**
     * Constructor
     *
     * @param DirectoryList $dirList
     * @param null|string $file
     * @throws \InvalidArgumentException
     */
    public function __construct(DirectoryList $dirList, $file = null)
    {
        $this->dirList = $dirList;
        if (null !== $file) {
            if (!preg_match('/^[a-z\d\.\-]+\.php$/i', $file)) {
                throw new \InvalidArgumentException("Invalid file name: {$file}");
            }
            $this->file = $file;
        } else {
            $this->file = self::DEFAULT_FILE;
        }
    }

    /**
     * Gets the file name
     *
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Loads the configuration file
     *
     * @return array
     */
    public function load()
    {
        $file = $this->dirList->getPath(DirectoryList::CONFIG) . '/' . $this->file;
        $result = @include $file;
        return $result ?: [];
    }
}
