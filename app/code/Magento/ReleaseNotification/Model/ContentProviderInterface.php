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

    /**
     * Returns the current Magento version used to retrieve the release notification content.
     * Version information after the dash (-) character is removed (ex. -dev or -rc).
     *
     * @return string
     */
    public function getTargetVersion();

    /**
     * Returns the Magento edition
     *
     * @return string
     */
    public function getEdition();

    /**
     * Returns the admin user's interface locale
     *
     * @return string
     */
    public function getLocale();
}
