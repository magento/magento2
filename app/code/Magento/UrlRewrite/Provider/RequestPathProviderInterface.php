<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\UrlRewrite\Provider;

/**
 * Interface RequestPathProviderInterface
 * @package Magento\UrlRewrite\Api\Provider
 */
interface RequestPathProviderInterface
{
    /**
     * Try found request_path by target_path
     *
     * @param string $targetPath
     * @return string|null
     */
    public function getRequestPath(string $targetPath): ?string;
}
