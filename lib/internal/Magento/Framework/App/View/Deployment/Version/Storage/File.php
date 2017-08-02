<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\View\Deployment\Version\Storage;

/**
 * Persistence of deployment version of static files in a local file
 * @since 2.0.0
 */
class File implements \Magento\Framework\App\View\Deployment\Version\StorageInterface
{
    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     * @since 2.0.0
     */
    private $directory;

    /**
     * @var string
     * @since 2.0.0
     */
    private $fileName;

    /**
     * @param \Magento\Framework\Filesystem $filesystem
     * @param string $directoryCode
     * @param string $fileName
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function load()
    {
        if ($this->directory->isReadable($this->fileName)) {
            return $this->directory->readFile($this->fileName);
        }
        return false;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function save($data)
    {
        $this->directory->writeFile($this->fileName, $data, 'w');
    }
}
