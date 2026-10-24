<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
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
