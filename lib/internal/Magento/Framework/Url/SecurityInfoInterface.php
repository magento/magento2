<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Url;

/**
 * URL security information. Answers whether URL is secured.
 *
 * @api
 */
interface SecurityInfoInterface
{
    /**
     * Check whether url is secure
     *
     * @param string $url
     * @return bool
     */
    public function isSecure($url);
}
