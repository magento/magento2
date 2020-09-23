<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\RemoteStorage\Plugin;

use Magento\RemoteStorage\Driver\DriverPool;
use Magento\RemoteStorage\Model\Config;
use Magento\Sitemap\Model\Sitemap as BaseSitemap;

/**
 * Plugin to replace file URL with remote URL.
 */
class Sitemap
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var DriverPool
     */
    private $driverPool;

    /**
     * @param DriverPool $driverPool
     * @param Config $config
     */
    public function __construct(DriverPool $driverPool, Config $config)
    {
        $this->driverPool = $driverPool;
        $this->config = $config;
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
        if ($this->config->isEnabled()) {
            $path = trim($sitemapPath . $sitemapFileName, '/');

            return $this->driverPool->getDriver()->getAbsolutePath('', $path);
        }

        return $result;
    }
}
