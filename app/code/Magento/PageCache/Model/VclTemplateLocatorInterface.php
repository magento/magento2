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
 */
interface VclTemplateLocatorInterface
{
    /**
     * Get Varnish Vcl template
     *
     * @param int $version
     * @return string
     * @throws UnsupportedVarnishVersion
     */
    public function getTemplate($version);
}
