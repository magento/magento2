<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
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
