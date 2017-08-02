<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Template\Html;

/**
 * HTML minifier
 *
 * @api
 * @since 2.0.0
 */
interface MinifierInterface
{
    /**
     * Return path to minified template file, or minify if file not exist
     *
     * @param string $file
     * @return string
     * @since 2.0.0
     */
    public function getMinified($file);

    /**
     * Return path to minified template file
     *
     * @param string $file
     * @return string
     * @since 2.0.0
     */
    public function getPathToMinified($file);

    /**
     * Minify template file
     *
     * @param string $file
     * @return void
     * @since 2.0.0
     */
    public function minify($file);
}
