<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Url;

/**
 * This ScopeInterface adds URL methods to the scope interface to help
 * determine scope based on URLs.
 *
 * @api
 * @since 100.0.2
 */
interface ScopeInterface extends \Magento\Framework\App\ScopeInterface
{
    /**
     * Retrieve base URL
     *
     * @param string $type
     * @param boolean|null $secure
     * @return string
     */
    public function getBaseUrl($type = '', $secure = null);

    /**
     * Check is URL should be secure
     *
     * @return boolean
     */
    public function isUrlSecure();
}
