<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Page;

/**
 * Favicon interface
 */
interface FaviconInterface
{
    /**
     * @return string
     */
    public function getFaviconFile();

    /**
     * @return string
     */
    public function getDefaultFavicon();
}
