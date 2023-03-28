<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\PageCache\Model;

use Magento\PageCache\Exception\UnsupportedVarnishVersion;

/**
 * Vcl template locator
 *
 * @api
 * @since 100.2.0
 */
interface VclTemplateLocatorInterface
{
    /**
     * Get Varnish Vcl template
     *
     * @param int $version
     * @param string $inputFile
     * @return string
     * @throws UnsupportedVarnishVersion
     * @since 100.2.0
     */
    public function getTemplate($version, $inputFile = null);
}
