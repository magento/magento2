<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Asset\File;

use Magento\Framework\View\Asset;

/**
 * A basic path context for assets that includes a directory path
 * @since 2.0.0
 */
class Context implements Asset\ContextInterface
{
    /**
     * @var string
     * @since 2.0.0
     */
    private $baseUrl;

    /**
     * @var string
     * @since 2.0.0
     */
    private $baseDir;

    /**
     * @var string
     * @since 2.0.0
     */
    private $path;

    /**
     * @param string $baseUrl
     * @param string $baseDirType
     * @param string $contextPath
     * @since 2.0.0
     */
    public function __construct($baseUrl, $baseDirType, $contextPath)
    {
        $this->baseUrl = $baseUrl;
        $this->baseDir = $baseDirType;
        $this->path = $contextPath;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * Get type of base directory
     *
     * @return string
     * @since 2.0.0
     */
    public function getBaseDirType()
    {
        return $this->baseDir;
    }
}
