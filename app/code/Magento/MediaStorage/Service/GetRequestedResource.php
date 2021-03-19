<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaStorage\Service;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\MediaStorage\Model\File\Storage\ConfigFactory;
use Magento\MediaStorage\Model\File\Storage\Request;

/**
 * Get requested Media resource.
 */
class GetRequestedResource
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var ConfigFactory
     */
    private $configFactory;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var string
     */
    private $configCacheFileRelativePath;

    /**
     * GetRequestedResource constructor.
     * @param Request $request
     * @param ConfigFactory $configFactory
     * @param Filesystem $filesystem
     * @param string $configCacheFileRelativePath
     */
    public function __construct(
        Request $request,
        ConfigFactory $configFactory,
        Filesystem $filesystem,
        string $configCacheFileRelativePath = ''
    ) {
        $this->request = $request;
        $this->configFactory = $configFactory;
        $this->filesystem = $filesystem;
        $this->configCacheFileRelativePath = $configCacheFileRelativePath;
    }

    /**
     * Get requested Media resource.
     *
     * @return bool
     */
    public function execute(): string
    {
        $pubDirectory = $this->filesystem->getDirectoryRead(DirectoryList::PUB)->getAbsolutePath();
        $relativePath = $this->request->getPathInfo();
        $fileAbsolutePath = $pubDirectory . $relativePath;
        $config = $this->configFactory->create(['cacheFile' => BP . '/' . $this->configCacheFileRelativePath]);
        $mediaDirectory = $config->getMediaDirectory();
        $resource = str_replace(rtrim($mediaDirectory, '/') . '/', '', $fileAbsolutePath);
        $allowedResources = $config->getAllowedResources();
        foreach ($allowedResources as $allowedResource) {
            if (0 === stripos($resource, $allowedResource)) {
                return $allowedResource;
            }
        }

        throw new LocalizedException(
            __('Media resource \'%1\' not allowed.', $resource)
        );
    }
}
