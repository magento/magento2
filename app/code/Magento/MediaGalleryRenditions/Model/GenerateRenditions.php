<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryRenditions\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Image\AdapterFactory;
use Magento\MediaGalleryApi\Api\IsPathExcludedInterface;
use Magento\MediaGalleryRenditionsApi\Api\GenerateRenditionsInterface;
use Magento\MediaGalleryRenditionsApi\Api\GetRenditionPathInterface;
use Psr\Log\LoggerInterface;

class GenerateRenditions implements GenerateRenditionsInterface
{
    private const IMAGE_FILE_NAME_PATTERN = '#\.(jpg|jpeg|gif|png)$# i';

    /**
     * @var AdapterFactory
     */
    private $imageFactory;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var GetRenditionPathInterface
     */
    private $getRenditionPath;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var File
     */
    private $driver;

    /**
     * @var IsPathExcludedInterface
     */
    private $isPathExcluded;

    /**
     * @var LoggerInterface
     */
    private $log;

    /**
     * @param AdapterFactory $imageFactory
     * @param Config $config
     * @param GetRenditionPathInterface $getRenditionPath
     * @param Filesystem $filesystem
     * @param File $driver
     * @param IsPathExcludedInterface $isPathExcluded
     * @param LoggerInterface $log
     */
    public function __construct(
        AdapterFactory $imageFactory,
        Config $config,
        GetRenditionPathInterface $getRenditionPath,
        Filesystem $filesystem,
        File $driver,
        IsPathExcludedInterface $isPathExcluded,
        LoggerInterface $log
    ) {
        $this->imageFactory = $imageFactory;
        $this->config = $config;
        $this->getRenditionPath = $getRenditionPath;
        $this->filesystem = $filesystem;
        $this->driver = $driver;
        $this->isPathExcluded = $isPathExcluded;
        $this->log = $log;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $paths): void
    {
        $failedPaths = [];

        foreach ($paths as $path) {
            try {
                $this->generateRendition($path);
            } catch (\Exception $exception) {
                $this->log->error($exception);
                $failedPaths[] = $path;
            }
        }

        if (!empty($failedPaths)) {
            throw new LocalizedException(
                __(
                    'Cannot create rendition for media asset paths: %paths',
                    [
                        'paths' => implode(', ', $failedPaths)
                    ]
                )
            );
        }
    }

    /**
     * Generate rendition for media asset path
     *
     * @param string $path
     * @throws FileSystemException
     * @throws LocalizedException
     * @throws \Exception
     */
    private function generateRendition(string $path): void
    {
        $this->validateAsset($path);

        $renditionPath = $this->getRenditionPath->execute($path);
        $this->createDirectory($renditionPath);

        $absolutePath = $this->getMediaDirectory()->getAbsolutePath($path);

        if ($this->shouldFileBeResized($absolutePath)) {
            $this->createResizedRendition(
                $absolutePath,
                $this->getMediaDirectory()->getAbsolutePath($renditionPath)
            );
        } else {
            $this->getMediaDirectory()->copyFile($path, $renditionPath);
        }
    }

    /**
     * Ensure valid media asset path is provided for renditions generation
     *
     * @param string $path
     * @throws FileSystemException
     * @throws LocalizedException
     */
    private function validateAsset(string $path): void
    {
        if (!$this->getMediaDirectory()->isFile($path)) {
            throw new LocalizedException(__('Media asset file %path does not exist!', ['path' => $path]));
        }

        if ($this->isPathExcluded->execute($path)) {
            throw new LocalizedException(
                __('Could not create rendition for image, path is restricted: %path', ['path' => $path])
            );
        }

        if (!preg_match(self::IMAGE_FILE_NAME_PATTERN, $path)) {
            throw new LocalizedException(
                __('Could not create rendition for image, unsupported file type: %path.', ['path' => $path])
            );
        }
    }

    /**
     * Create directory for rendition file
     *
     * @param string $path
     * @throws LocalizedException
     */
    private function createDirectory(string $path): void
    {
        try {
            $this->getMediaDirectory()->create($this->driver->getParentDirectory($path));
        } catch (\Exception $exception) {
            throw new LocalizedException(__('Cannot create directory for rendition %path', ['path' => $path]));
        }
    }

    /**
     * Create rendition file
     *
     * @param string $absolutePath
     * @param string $absoluteRenditionPath
     * @throws \Exception
     */
    private function createResizedRendition(string $absolutePath, string $absoluteRenditionPath): void
    {
        $image = $this->imageFactory->create();
        $image->open($absolutePath);
        $image->keepAspectRatio(true);
        $image->resize($this->config->getWidth(), $this->config->getHeight());
        $image->save($absoluteRenditionPath);
    }

    /**
     * Check if image needs to resize or not
     *
     * @param string $absolutePath
     * @return bool
     */
    private function shouldFileBeResized(string $absolutePath): bool
    {
        [$width, $height] = getimagesize($absolutePath);
        return $width > $this->config->getWidth() || $height > $this->config->getHeight();
    }

    /**
     * Retrieve a media directory instance with write permissions
     *
     * @return WriteInterface
     * @throws FileSystemException
     */
    private function getMediaDirectory(): WriteInterface
    {
        return $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
    }
}
