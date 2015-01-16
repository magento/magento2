<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Url;

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
