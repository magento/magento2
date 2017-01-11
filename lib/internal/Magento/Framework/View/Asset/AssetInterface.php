<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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

    /**
     * Retrieve source content type
     *
     * @return string
     */
    public function getSourceContentType();
}
