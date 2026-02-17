<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\UrlRewriteGraphQl\Model\Resolver\UrlRewrite;

/**
 * Interface for resolution of custom URLs.
 *
 * It can be used, for example, to resolve '\' URL path to a 'Home' page.
 *
 * @api
 */
interface CustomUrlLocatorInterface
{
    /**
     * Resolve URL based on custom rules.
     *
     * @param string $urlKey
     * @return string|null Return null if URL cannot be resolved
     */
    public function locateUrl($urlKey): ?string;
}
