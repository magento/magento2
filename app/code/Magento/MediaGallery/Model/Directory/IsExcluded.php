<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGallery\Model\Directory;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Filesystem\File\WriteInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\MediaGalleryApi\Api\IsPathExcludedInterface;
use Magento\MediaGalleryApi\Model\ExcludedPatternsConfigInterface;

/**
 * Check if the path is excluded for media gallery. Directory path may be blacklisted if it's reserved by the system
 */
class IsExcluded implements IsPathExcludedInterface
{
    /**
     * @var ExcludedPatternsConfigInterface
     */
    private $config;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var WriteInterface
     */
    private $mediaDirectory;

    /**
     * @param ExcludedPatternsConfigInterface $config
     * @param Filesystem|null $filesystem
     */
    public function __construct(ExcludedPatternsConfigInterface $config, Filesystem $filesystem = null)
    {
        $this->config = $config;
        $this->filesystem = $filesystem ?: ObjectManager::getInstance()->get(Filesystem::class);
        $this->mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
    }

    /**
     * Check if the directory path can be used in the media gallery operations
     *
     * @param string $path
     * @return bool
     */
    public function execute(string $path): bool
    {
        $realPath = $this->mediaDirectory->getDriver()->getRealPathSafety($path);
        foreach ($this->config->get() as $pattern) {
            if (empty($pattern)) {
                continue;
            }
            preg_match($pattern, $realPath, $result);

            if ($result) {
                return true;
            }
        }

        return false;
    }
}
