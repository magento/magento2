<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Model;

/**
 * Downloadable component interface
 *
 * @api
 * @since 100.0.2
 */
interface ComponentInterface
{
    /**
     * Retrieve Base files path
     *
     * @return string
     */
    public function getBasePath();

    /**
     * Retrieve base temporary path
     *
     * @return string
     */
    public function getBaseTmpPath();

    /**
     * Retrieve links searchable data
     *
     * @param int $productId
     * @param int $storeId
     * @return array
     */
    public function getSearchableData($productId, $storeId);
}
