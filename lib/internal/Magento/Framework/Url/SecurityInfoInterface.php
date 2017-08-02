<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Url;

/**
 * URL security information. Answers whether URL is secured.
 *
 * @api
 * @since 2.0.0
 */
interface SecurityInfoInterface
{
    /**
     * Check whether url is secure
     *
     * @param string $url
     * @return bool
     * @since 2.0.0
     */
    public function isSecure($url);
}
