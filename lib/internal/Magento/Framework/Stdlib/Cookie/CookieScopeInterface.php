<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Stdlib\Cookie;

/**
 * CookieScope is used to store default scope metadata.
 * @api
 * @since 2.0.0
 */
interface CookieScopeInterface
{
    /**
     * Merges the input override metadata with any defaults set on this Scope, and then returns a CookieMetadata
     * object representing the merged values.
     *
     * @param SensitiveCookieMetadata|null $override
     * @return SensitiveCookieMetadata
     * @since 2.0.0
     */
    public function getSensitiveCookieMetadata(SensitiveCookieMetadata $override = null);

    /**
     * Merges the input override metadata with any defaults set on this Scope, and then returns a CookieMetadata
     * object representing the merged values.
     *
     * @param PublicCookieMetadata|null $override
     * @return PublicCookieMetadata
     * @since 2.0.0
     */
    public function getPublicCookieMetadata(PublicCookieMetadata $override = null);

    /**
     * Merges the input override metadata with any defaults set on this Scope, and then returns a CookieMetadata
     * object representing the merged values.
     *
     * @param CookieMetadata|null $override
     * @return CookieMetadata
     * @since 2.0.0
     */
    public function getCookieMetadata(CookieMetadata $override = null);
}
