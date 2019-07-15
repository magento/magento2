<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\MediaStorage\Api;

use Magento\Framework\Model\AbstractModel;

/**
 * Class Storage
 *
 * @api
 * @since 100.0.2
 */
interface StorageInterface
{
    /**
     * Storage systems ids
     */
    const STORAGE_MEDIA_FILE_SYSTEM = 0;

    const STORAGE_MEDIA_DATABASE = 1;

    /**
     * Config paths for storing storage configuration
     */
    const XML_PATH_STORAGE_MEDIA = 'system/media_storage_configuration/media_storage';

    const XML_PATH_STORAGE_MEDIA_DATABASE = 'system/media_storage_configuration/media_database';

    const XML_PATH_MEDIA_UPDATE_TIME = 'system/media_storage_configuration/configuration_update_time';

    const XML_PATH_MEDIA_RESOURCE_WHITELIST = 'system/media_storage_configuration/allowed_resources';

    /**
     * Retrieve storage model
     * If storage not defined - retrieve current storage
     *
     * params = array(
     *  connection  => string,  - define connection for model if needed
     *  init        => bool     - force initialization process for storage model
     * )
     *
     * @param int|null $storage
     * @param array    $params
     * @return AbstractModel|bool
     */
    public function getStorageModel($storage = null, $params = []);

    /**
     * Return current media directory, allowed resources for get.php script, etc.
     *
     * @return array
     */
    public function getScriptConfig();
}
