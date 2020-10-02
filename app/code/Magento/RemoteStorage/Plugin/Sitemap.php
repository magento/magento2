<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\RemoteStorage\Plugin;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\RemoteStorage\Driver\DriverPool;
use Magento\RemoteStorage\Filesystem;
use Magento\RemoteStorage\Model\Config;
use Magento\Sitemap\Model\Sitemap as BaseSitemap;

/**
 * Plugin to replace file URL with remote URL.
 */
class Sitemap
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var bool
     */
    private $isEnabled;

    /**
     * @param Filesystem $filesystem
     * @param Config $config
     */
    public function __construct(Filesystem $filesystem, Config $config)
    {
        $this->filesystem = $filesystem;
        $this->isEnabled = $config->isEnabled();
    }

    /**
     * Modifies image URl to point to correct remote storage.
     *
     * @param BaseSitemap $subject
     * @param string $result
     * @param string $sitemapPath
     * @param string $sitemapFileName
     * @return string
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetSitemapUrl(
        BaseSitemap $subject,
        string $result,
        string $sitemapPath,
        string $sitemapFileName
    ): string {
        if ($this->isEnabled) {
            $path = trim($sitemapPath . $sitemapFileName, '/');

            return $this->filesystem->getDirectoryRead(DirectoryList::ROOT, DriverPool::REMOTE)
                ->getAbsolutePath($path);
        }

        return $result;
    }
}
