<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Asset;

/**
 * View asset configuration interface
 */
interface ConfigInterface
{
    /**
     * Check whether merging of CSS files is on
     *
     * @return bool
     */
    public function isMergeCssFiles();

    /**
     * Check whether merging of JavScript files is on
     *
     * @return bool
     */
    public function isMergeJsFiles();

    /**
     * Check whether asset minification is on for specified content type
     *
     * @param string $contentType
     * @return bool
     */
    public function isAssetMinification($contentType);

    /**
     * Get asset minification adapter for specified content type
     *
     * @param string $contentType
     * @return string
     */
    public function getAssetMinificationAdapter($contentType);
}
