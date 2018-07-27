<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Stdlib\Cookie;

/**
 * CookieScope is used to store default scope metadata.
 * @api
 */
interface CookieScopeInterface
{
    /**
     * Merges the input override metadata with any defaults set on this Scope, and then returns a CookieMetadata
     * object representing the merged values.
     *
     * @param SensitiveCookieMetadata|null $override
     * @return SensitiveCookieMetadata
     */
    public function getSensitiveCookieMetadata(SensitiveCookieMetadata $override = null);

    /**
     * Merges the input override metadata with any defaults set on this Scope, and then returns a CookieMetadata
     * object representing the merged values.
     *
     * @param PublicCookieMetadata|null $override
     * @return PublicCookieMetadata
     */
    public function getPublicCookieMetadata(PublicCookieMetadata $override = null);

    /**
     * Merges the input override metadata with any defaults set on this Scope, and then returns a CookieMetadata
     * object representing the merged values.
     *
     * @param CookieMetadata|null $override
     * @return CookieMetadata
     */
    public function getCookieMetadata(CookieMetadata $override = null);
}
