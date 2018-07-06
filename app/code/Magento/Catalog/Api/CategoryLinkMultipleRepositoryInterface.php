<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Api;

/**
 * @since 102.0.5
 */
interface CategoryLinkMultipleRepositoryInterface
{
    /**
     * Replace category products
     *
     * @param int $categoryId
     * @param \Magento\Catalog\Api\Data\CategoryProductLinkInterface[] $productLinks
     * @return bool will returned True if assigned
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function replace($categoryId, $productLinks);

    /**
     * Save multiple category products
     *
     * @param int $categoryId
     * @param \Magento\Catalog\Api\Data\CategoryProductLinkInterface[] $productLinks
     * @return bool will returned True if assigned
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function saveMultiple($categoryId, $productLinks);
}
