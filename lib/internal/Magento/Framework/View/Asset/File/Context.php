<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Asset\File;

use Magento\Framework\View\Asset;

/**
 * A basic path context for assets that includes a directory path
 */
class Context implements Asset\ContextInterface
{
    /**
     * @var string
     */
    private $baseUrl;

    /**
     * @var string
     */
    private $baseDir;

    /**
     * @var string
     */
    private $path;

    /**
     * @param string $baseUrl
     * @param string $baseDirType
     * @param string $contextPath
     */
    public function __construct($baseUrl, $baseDirType, $contextPath)
    {
        $this->baseUrl = $baseUrl;
        $this->baseDir = $baseDirType;
        $this->path = $contextPath;
    }

    /**
     * {@inheritdoc}
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * Get type of base directory
     *
     * @return string
     */
    public function getBaseDirType()
    {
        return $this->baseDir;
    }
}
