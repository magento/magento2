<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ReleaseNotification\Model;

/**
 * Class ContentProviderInterface
 *
 * Requests the release notification content data from a defined service
 */
interface ContentProviderInterface
{
    /**
     * Retrieves the release notification content data.
     *
     * Returns received content or FALSE in case of failure.
     *
     * @return string|false
     */
    public function getContent();
}
