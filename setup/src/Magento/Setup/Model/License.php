<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

/**
 * License file reader
 *
 * @package Magento\Setup\Model
 */
class License
{
    /**
     * Default License File location
     *
     * @var string
     */
    const DEFAULT_LICENSE_FILENAME = 'LICENSE.txt';

    /**
     * License File location
     *
     * @var string
     */
    const LICENSE_FILENAME = 'LICENSE_EE.txt';

    /**
     * Directory that contains license file
     *
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface
     */
    private $dir;

    /**
     * Constructor
     *
     * @param Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->dir = $filesystem->getDirectoryRead(DirectoryList::ROOT);
    }

    /**
     * Returns contents of License file.
     *
     * @return string|boolean
     */
    public function getContents()
    {
        if ($this->dir->isFile(self::LICENSE_FILENAME)) {
            return $this->dir->readFile(self::LICENSE_FILENAME);
        } elseif ($this->dir->isFile(self::DEFAULT_LICENSE_FILENAME)) {
            return $this->dir->readFile(self::DEFAULT_LICENSE_FILENAME);
        } else {
            return false;
        }
    }
}
