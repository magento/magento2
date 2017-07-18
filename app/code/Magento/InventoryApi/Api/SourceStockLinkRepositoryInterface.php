<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InventoryApi\Api;

/**
 * Interface to assign specific source to particular stock
 *
 * @api
 */
interface SourceStockLinkRepositoryInterface
{
    /**
     * Save SourceStockLink data
     *
     * @param \Magento\InventoryApi\Api\Data\SourceStockLinkInterface $sourceStockLink
     * @return void
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(\Magento\InventoryApi\Api\Data\SourceStockLinkInterface $sourceStockLink);

    /**
     * Remove the source assignment from the stock.
     *
     * @param \Magento\InventoryApi\Api\Data\SourceStockLinkInterface $sourceStockLink
     * @return void
     *
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function delete(\Magento\InventoryApi\Api\Data\SourceStockLinkInterface $sourceStockLink);
}
