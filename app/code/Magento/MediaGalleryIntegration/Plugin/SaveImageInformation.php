<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryIntegration\Plugin;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\File\Uploader;
use Magento\Framework\Filesystem;
use Magento\MediaGalleryApi\Api\IsPathExcludedInterface;
use Magento\MediaGalleryApi\Api\SaveAssetsInterface;
use Magento\MediaGallerySynchronizationApi\Api\SynchronizeFilesInterface;
use Magento\MediaGalleryUiApi\Api\ConfigInterface;
use Psr\Log\LoggerInterface;

/**
 * Save image information by SaveAssetsInterface.
 */
class SaveImageInformation
{
    /**
     * @var IsPathExcludedInterface
     */
    private $isPathExcluded;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var LoggerInterface
     */
    private $log;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var SynchronizeFilesInterface
     */
    private $synchronizeFiles;

    /**
     * @var string[]
     */
    private $imageExtensions;

    /**
     * @param Filesystem $filesystem
     * @param LoggerInterface $log
     * @param IsPathExcludedInterface $isPathExcluded
     * @param SynchronizeFilesInterface $synchronizeFiles
     * @param ConfigInterface $config
     * @param array $imageExtensions
     */
    public function __construct(
        Filesystem $filesystem,
        LoggerInterface $log,
        IsPathExcludedInterface $isPathExcluded,
        SynchronizeFilesInterface $synchronizeFiles,
        ConfigInterface $config,
        array $imageExtensions
    ) {
        $this->log = $log;
        $this->isPathExcluded = $isPathExcluded;
        $this->filesystem = $filesystem;
        $this->synchronizeFiles = $synchronizeFiles;
        $this->config = $config;
        $this->imageExtensions = $imageExtensions;
    }

    /**
     * Saves asset to media gallery after save image.
     *
     * @param Uploader $subject
     * @param array $result
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @return array
     */
    public function afterSave(Uploader $subject, array $result): array
    {
        if (!$this->config->isEnabled()) {
            return $result;
        }

        $path = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)
            ->getRelativePath(rtrim($result['path'], '/') . '/' . ltrim($result['file'], '/'));
        if (!$this->isApplicable($path)) {
            return $result;
        }
        $this->synchronizeFiles->execute([$path]);

        return $result;
    }

    /**
     * Can asset be saved with provided path
     *
     * @param string $path
     * @return bool
     */
    private function isApplicable(string $path): bool
    {
        try {
            return $path
                && !$this->isPathExcluded->execute($path)
                && preg_match('#\.(' . implode("|", $this->imageExtensions) . ')$# i', $path);
        } catch (\Exception $exception) {
            $this->log->critical($exception);
            return false;
        }
    }
}
