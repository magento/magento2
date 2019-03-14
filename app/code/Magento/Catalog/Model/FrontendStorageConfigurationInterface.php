<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model;

/**
 * @api
 * Storage, which provide information for frontend storages, as product-storage, ids-storage
 * @since 102.0.0
 */
interface FrontendStorageConfigurationInterface
{
    /**
     * Lifetime is not mandatory attribute for each frontend storage configuration scope. However in some cases
     * (e.g. when we need to flush deprecated frontend actions) we need to have default lifetime
     */
    const DEFAULT_LIFETIME = 1000;

    /**
     * Prepare dynamic data which will be used in Storage Configuration (e.g. data from App/Config)
     *
     * @return array
     * @since 102.0.0
     */
    public function get();
}
