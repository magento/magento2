<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Interface for minification adapters
 */
namespace Magento\Framework\Code\Minifier;

interface AdapterInterface
{
    /**
     * Minify content
     *
     * @param string $content
     * @return string
     */
    public function minify($content);
}
