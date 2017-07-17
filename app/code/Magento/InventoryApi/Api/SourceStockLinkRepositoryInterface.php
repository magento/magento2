<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InventoryApi\Api;

/**
 * Interface to assign specific source to particular stock.
 *
 * @api
 */
interface SourceStockLinkRepositoryInterface
{

    /**
     * Get source by given Id.
     *
     * @param int $linkId
     * @return \Magento\InventoryApi\Api\Data\SourceStockLinkInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($linkId);

    /**
     * Assign a source a to a stock.
     *
     * @param \Magento\InventoryApi\Api\Data\SourceStockLinkInterface $sourceStockLink
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @return void
     */
    public function save(\Magento\InventoryApi\Api\Data\SourceStockLinkInterface $sourceStockLink);

    /**
     * Remove the source assignment from the stock.
     *
     * @param \Magento\InventoryApi\Api\Data\SourceStockLinkInterface $sourceStockLink
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     * @return void
     */
    public function delete(\Magento\InventoryApi\Api\Data\SourceStockLinkInterface $sourceStockLink);

    /**
     * Remove the source assignment from the stock by id.
     *
     * @param int $linkId
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     * @return void
     */
    public function deleteById($linkId);

}
