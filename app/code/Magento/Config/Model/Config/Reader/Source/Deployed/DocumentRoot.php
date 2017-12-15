<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Config\Model\Config\Reader\Source\Deployed;

use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\DeploymentConfig;

/**
 * Class DocumentRoot
 * @package Magento\Config\Model\Config\Reader\Source\Deployed
 * @api
 * @since 100.2.0
 */
class DocumentRoot
{
    /**
     * @var DeploymentConfig
     */
    private $config;

    /**
     * DocumentRoot constructor.
     * @param DeploymentConfig $config
     */
    public function __construct(DeploymentConfig $config)
    {
        $this->config = $config;
    }

    /**
     * A shortcut to load the document root path from the DirectoryList based on the
     * deployment configuration.
     *
     * @return string
     * @since 100.2.0
     */
    public function getPath()
    {
        return $this->isPub() ? DirectoryList::PUB : DirectoryList::ROOT;
    }

    /**
     * Returns whether the deployment configuration specifies that the document root is
     * in the pub/ folder. This affects ares such as sitemaps and robots.txt (and will
     * likely be extended to control other areas).
     *
     * @return bool
     * @since 100.2.0
     */
    public function isPub()
    {
        return (bool)$this->config->get(ConfigOptionsListConstants::CONFIG_PATH_DOCUMENT_ROOT_IS_PUB);
    }
}
