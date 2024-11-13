<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Api;

/**
 * Interface for Bulk children addition
 */
interface ProductLinkManagementAddChildrenInterface
{
    /**
     * Bulk add children operation
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @param int $optionId
     * @param \Magento\Bundle\Api\Data\LinkInterface[] $linkedProducts
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @return void
     */
    public function addChildren(
        \Magento\Catalog\Api\Data\ProductInterface $product,
        int $optionId,
        array $linkedProducts
    );
}
