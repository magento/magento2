<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\PageCache\Model;

use Magento\PageCache\Exception\UnsupportedVarnishVersion;

/**
 * Vcl template locator interface
 *
 * @api
 * @since 2.2.0
 */
interface VclTemplateLocatorInterface
{
    /**
     * Get Varnish Vcl template
     *
     * @param int $version
     * @return string
     * @throws UnsupportedVarnishVersion
     * @since 2.2.0
     */
    public function getTemplate($version);
}
