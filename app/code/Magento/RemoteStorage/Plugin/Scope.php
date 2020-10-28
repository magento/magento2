<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\RemoteStorage\Plugin;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Url\ScopeInterface;
use Magento\Framework\UrlInterface;
use Magento\RemoteStorage\Driver\DriverPool;
use Magento\RemoteStorage\Model\Config;
use Magento\RemoteStorage\Filesystem;

/**
 * Modifies the base URL.
 */
class Scope
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
     * @param Config $config
     * @param Filesystem $filesystem
     */
    public function __construct(Config $config, Filesystem $filesystem)
    {
        $this->isEnabled = $config->isEnabled() && $config->getExposeUrls();
        $this->filesystem = $filesystem;
    }

    /**
     * Modifies the base URL.
     *
     * @param ScopeInterface $subject
     * @param string $result
     * @param string $type
     * @return string
     * @throws ValidatorException
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetBaseUrl(ScopeInterface $subject, string $result, string $type = ''): string
    {
        if ($type === UrlInterface::URL_TYPE_MEDIA && $this->isEnabled) {
            return $this->filesystem->getDirectoryRead(DirectoryList::MEDIA, DriverPool::REMOTE)
                ->getAbsolutePath();
        }

        return $result;
    }
}
