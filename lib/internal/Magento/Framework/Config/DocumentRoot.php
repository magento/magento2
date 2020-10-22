<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Config;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\DeploymentConfig;

/**
 * Document root detector.
 * @deprecared Magento always uses the pub directory
 * @api
 */
class DocumentRoot
{
    /**
     * @var DeploymentConfig
     */
    private $config;

    /**
     * @param DeploymentConfig $config
     */
    public function __construct(DeploymentConfig $config)
    {
        $this->config = $config;
    }

    /**
     * A shortcut to load the document root path from the DirectoryList.
     *
     * @return string
     */
    public function getPath(): string
    {
        return DirectoryList::PUB;
    }

    /**
     * Checks if root folder is /pub.
     *
     * @return bool
     */
    public function isPub(): bool
    {
        return true;
    }
}
