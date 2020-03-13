<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Storage;

use League\Flysystem\Filesystem;

/**
 * File storage abstraction
 */
class Storage implements StorageInterface
{

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * Storage constructor.
     *
     * @param Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * @inheritDoc
     */
    public function put($path, $contents, array $config = []): bool
    {
        return $this->filesystem->put($path, $contents, $config);
    }

    /**
     * @inheritDoc
     */
    public function deleteDir($dirname): bool
    {
        try {
            $result = $this->filesystem->deleteDir($dirname);
        } catch (\League\Flysystem\RootViolationException $exception) {
            throw new \Magento\Framework\Storage\RootViolationException($exception->getMessage());
        }
        return $result;
    }

    /**
     * @inheritDoc
     */
    public function getMetadata($path): ?array
    {
        try {
            $metadata = $this->filesystem->getMetadata($path);
        } catch (\League\Flysystem\FileNotFoundException $exception) {
            throw new \Magento\Framework\Storage\FileNotFoundException(
                $exception->getMessage()
            );
        }
        if ($metadata === false) {
            $metadata = null;
        }
        return $metadata;
    }

    /**
     * @inheritDoc
     */
    public function has($path): bool
    {
        return $this->filesystem->has($path);
    }
}
