<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
     * Check whether bundling of JavScript files is on
     *
     * @return bool
     */
    public function isBundlingJsFiles();

    /**
     * Check whether minify of HTML is on
     *
     * @return bool
     */
    public function isMinifyHtml();
}
