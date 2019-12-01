<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Api;

/**
 * @api
 * @since 100.0.2
 */
interface CategoryListRepositoryAdditionalInterface
{
    /**
     * delete by skus list
     *
     * @param int   $categoryId
     * @param array $productSkuList
     * @return bool
     *
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     */
    public function deleteBySkus($categoryId, array $productSkuList);
}
