<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ReleaseNotification\Model;

/**
 * Requests the release notification content data from a defined service
 * @api
 * @deprecated Starting from Magento OS 2.4.7 Magento_ReleaseNotification module is deprecated
 * in favor of another in-product messaging mechanism
 * @see Current in-product messaging mechanism
 */
interface ContentProviderInterface
{
    /**
     * Retrieves the release notification content data.
     *
     * @param string $version
     * @param string $edition
     * @param string $locale
     *
     * @return string|false
     */
    public function getContent($version, $edition, $locale);
}
