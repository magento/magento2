<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\RemoteStorage\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Filesystem\DriverPool;

/**
 * Configuration for remote storage.
 */
class Config
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Check if remote FS is enabled.
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        $driver = $this->scopeConfig->getValue('system/file_system/driver');

        return $driver && $driver !== DriverPool::FILE;
    }
}
