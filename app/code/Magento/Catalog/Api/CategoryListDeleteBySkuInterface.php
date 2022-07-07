<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Api;

/**
 * @api
 * @since 104.0.0
 */
interface CategoryListDeleteBySkuInterface
{
    /**
     * Delete by skus list
     *
     * @param int      $categoryId
     * @param string[] $productSkuList
     * @return bool
     *
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\InputException
     * @since 104.0.0
     */
    public function deleteBySkus(int $categoryId, array $productSkuList): bool;
}
