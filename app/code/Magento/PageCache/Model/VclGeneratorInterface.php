<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\PageCache\Model;

/**
 * @api
 * @since 2.2.0
 */
interface VclGeneratorInterface
{
    /**
     * Return generated varnish.vcl configuration file
     *
     * @param int $version
     * @return string
     * @since 2.2.0
     */
    public function generateVcl($version);
}
