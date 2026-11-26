<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
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
            // The version is a URL path segment: a trailing newline in a file written by a deploy
            // script (e.g. "date +%s > deployed_version.txt") would end up in every static URL.
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
