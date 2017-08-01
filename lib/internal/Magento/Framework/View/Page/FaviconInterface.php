<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Page;

/**
 * Favicon interface
 *
 * @api
 * @since 2.0.0
 */
interface FaviconInterface
{
    /**
     * @return string
     * @since 2.0.0
     */
    public function getFaviconFile();

    /**
     * @return string
     * @since 2.0.0
     */
    public function getDefaultFavicon();
}
