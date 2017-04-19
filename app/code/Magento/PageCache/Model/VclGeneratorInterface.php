<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\PageCache\Model;

/**
 * @api
 */
interface VclGeneratorInterface
{
    /**
     * Return generated varnish.vcl configuration file
     *
     * @param int $version
     * @return string
     */
    public function generateVcl($version);
}
