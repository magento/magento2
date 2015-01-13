<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Downloadable\Model;

/**
 * Downloadable link model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
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
