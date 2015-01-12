<?php
/**
 * Url security information. Answers whether url is secured
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Url;

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
