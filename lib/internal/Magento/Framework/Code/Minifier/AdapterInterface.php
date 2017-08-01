<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Interface for minification adapters
 */
namespace Magento\Framework\Code\Minifier;

/**
 * Interface \Magento\Framework\Code\Minifier\AdapterInterface
 *
 * @since 2.0.0
 */
interface AdapterInterface
{
    /**
     * Minify content
     *
     * @param string $content
     * @return string
     * @since 2.0.0
     */
    public function minify($content);
}
