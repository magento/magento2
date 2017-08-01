<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Asset;

/**
 * An abstraction for static view file (or resource) that may be embedded to a web page
 *
 * @api
 * @since 2.0.0
 */
interface AssetInterface
{
    /**
     * Retrieve URL pointing to a resource
     *
     * @return string
     * @since 2.0.0
     */
    public function getUrl();

    /**
     * Retrieve type of contents
     *
     * @return string
     * @since 2.0.0
     */
    public function getContentType();

    /**
     * Retrieve source content type
     *
     * @return string
     * @since 2.2.0
     */
    public function getSourceContentType();
}
