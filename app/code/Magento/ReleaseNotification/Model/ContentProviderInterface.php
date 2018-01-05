<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ReleaseNotification\Model;

/**
 * Requests the release notification content data from a defined service
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
