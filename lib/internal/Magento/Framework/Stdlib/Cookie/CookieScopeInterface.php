<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Stdlib\Cookie;

/**
 * CookieScope is used to store default scope metadata.
 */
interface CookieScopeInterface
{
    /**
     * Merges the input override metadata with any defaults set on this Scope, and then returns a CookieMetadata
     * object representing the merged values.
     *
     * @param SensitiveCookieMetadata|null $override
     * @return SensitiveCookieMetadata
     * @api
     */
    public function getSensitiveCookieMetadata(SensitiveCookieMetadata $override = null);

    /**
     * Merges the input override metadata with any defaults set on this Scope, and then returns a CookieMetadata
     * object representing the merged values.
     *
     * @param PublicCookieMetadata|null $override
     * @return PublicCookieMetadata
     * @api
     */
    public function getPublicCookieMetadata(PublicCookieMetadata $override = null);

    /**
     * Merges the input override metadata with any defaults set on this Scope, and then returns a CookieMetadata
     * object representing the merged values.
     *
     * @param CookieMetadata|null $override
     * @return CookieMetadata
     * @api
     */
    public function getCookieMetadata(CookieMetadata $override = null);
}
