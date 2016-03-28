<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Asset;

/**
 * An abstraction for static view file (or resource) that may be embedded to a web page
 */
interface AssetInterface
{
    /**
     * Retrieve URL pointing to a resource
     *
     * @return string
     */
    public function getUrl();

    /**
     * Retrieve type of contents
     *
     * @return string
     */
    public function getContentType();
}
