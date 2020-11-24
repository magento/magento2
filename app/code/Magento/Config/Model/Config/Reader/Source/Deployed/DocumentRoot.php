<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model\Config\Reader\Source\Deployed;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Document root detector.
 *
 * @api
 * @since 101.0.0
 *
 * @deprecated Magento always uses the pub directory
 * @see DirectoryList::PUB
 */
class DocumentRoot
{
    /**
     * @param DeploymentConfig $config
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(DeploymentConfig $config)
    {
    }

    /**
     * A shortcut to load the document root path from the DirectoryList.
     *
     * @return string
     * @since 101.0.0
     */
    public function getPath()
    {
        return DirectoryList::PUB;
    }

    /**
     * Checks if root folder is /pub.
     *
     * @return bool
     * @since 101.0.0
     */
    public function isPub()
    {
        return true;
    }
}
