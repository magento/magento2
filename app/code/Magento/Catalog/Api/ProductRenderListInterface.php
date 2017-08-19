<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Api;

/**
 * Interface which provides product renders information for products
 * @api
 */
interface ProductRenderListInterface
{
    /**
     * Collect and retrieve the list of product render info
     * This info contains raw prices and formated prices, product name, stock status, store_id, etc
     * @see \Magento\Catalog\Api\Data\ProductRenderInfoDtoInterface
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @param int $storeId
     * @param string $currencyCode
     * @return \Magento\Catalog\Api\Data\ProductRenderSearchResultsInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria, $storeId, $currencyCode);
}
