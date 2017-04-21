<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\View\Deployment\Version\Storage;

/**
 * Persistence of deployment version of static files in a local file
 */
class File implements \Magento\Framework\App\View\Deployment\Version\StorageInterface
{
    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    private $directory;

    /**
     * @var string
     */
    private $fileName;

    /**
     * @param \Magento\Framework\Filesystem $filesystem
     * @param string $directoryCode
     * @param string $fileName
     */
    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        $directoryCode,
        $fileName
    ) {
        $this->directory = $filesystem->getDirectoryWrite($directoryCode);
        $this->fileName = $fileName;
    }

    /**
     * {@inheritdoc}
     */
    public function load()
    {
        if ($this->directory->isReadable($this->fileName)) {
            return trim($this->directory->readFile($this->fileName));
        }
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function save($data)
    {
        $this->directory->writeFile($this->fileName, $data, 'w');
    }
}
