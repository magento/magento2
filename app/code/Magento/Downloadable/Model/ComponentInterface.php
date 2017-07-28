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
 * @since 2.0.0
 */
interface ComponentInterface
{
    /**
     * Retrieve Base files path
     *
     * @return string
     * @since 2.0.0
     */
    public function getBasePath();

    /**
     * Retrieve base temporary path
     *
     * @return string
     * @since 2.0.0
     */
    public function getBaseTmpPath();

    /**
     * Retrieve links searchable data
     *
     * @param int $productId
     * @param int $storeId
     * @return array
     * @since 2.0.0
     */
    public function getSearchableData($productId, $storeId);
}
