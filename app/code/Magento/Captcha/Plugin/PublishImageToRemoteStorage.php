<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Captcha\Plugin;

use Magento\Captcha\Model\DefaultModel;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\TargetDirectory;
use Magento\Framework\Filesystem\DriverPool;

/**
 * Moves the generated CAPTCHA image to the configured media storage.
 *
 * The image itself has to be rendered by GD to the local filesystem, as the underlying
 * Laminas implementation writes it with native functions. When remote storage is enabled
 * the media base URL is served from the remote storage, so the image has to be moved there,
 * otherwise it is not reachable for the browser and every other application node.
 *
 * @see \Magento\Captcha\Helper\Data::getImgDir()
 */
class PublishImageToRemoteStorage
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var TargetDirectory
     */
    private $targetDirectory;

    /**
     * @param Filesystem $filesystem
     * @param TargetDirectory $targetDirectory
     */
    public function __construct(Filesystem $filesystem, TargetDirectory $targetDirectory)
    {
        $this->filesystem = $filesystem;
        $this->targetDirectory = $targetDirectory;
    }

    /**
     * Move the locally generated image to the target media storage.
     *
     * @param DefaultModel $subject
     * @param string $result
     * @return string
     * @throws FileSystemException
     */
    public function afterGenerate(DefaultModel $subject, $result)
    {
        $localDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA, DriverPool::FILE);
        $targetDirectory = $this->targetDirectory->getDirectoryWrite(DirectoryList::MEDIA);

        if (get_class($targetDirectory->getDriver()) === get_class($localDirectory->getDriver())) {
            return $result;
        }

        $sourcePath = $subject->getImgDir() . $result . $subject->getSuffix();
        if (!$localDirectory->getDriver()->isFile($sourcePath)) {
            return $result;
        }

        $localDirectory->getDriver()->rename(
            $sourcePath,
            $targetDirectory->getAbsolutePath($localDirectory->getRelativePath($sourcePath)),
            $targetDirectory->getDriver()
        );

        return $result;
    }
}
