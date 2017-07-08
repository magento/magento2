<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InventoryApi\Api;

/**
 * Add Api functionality to handle source and stock link assignment.  
 *
 * @api
 */
interface SourceStockLinkRepositoryInterface
{

    /**
     * Assign a source a to a stock.
     *
     * @param \Magento\InventoryApi\Api\Data\SourceStockLinkInterface $sourceStockLink
     * @return bool will returned True if assigned
     *
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function save(\Magento\InventoryApi\Api\Data\SourceStockLinkInterface $sourceStockLink);

    /**
     * Remove the source assignment from the stock.
     *
     * @param \Magento\InventoryApi\Api\Data\SourceStockLinkInterface $sourceStockLink
     * @return bool will returned True if assigned
     *
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function delete(\Magento\InventoryApi\Api\Data\SourceStockLinkInterface $sourceStockLink);

}
