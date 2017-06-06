<?php

namespace Magento\CatalogInventory\Api;

use Magento\CatalogInventory\Api\Data\InventoryRecordInterface;
use Magento\Framework\Search\RequestInterface;

/**
 * Inventory validator interface
 *
 * It is aimed to be the only place where inventory is validated, when item is added to card,
 * item is reserved and so on.
 *
 * @api
 */
interface InventoryValidatorInterface
{
    /**
     * Validates inventory requests based on registry response
     *
     * @param InventoryRecordCollectionInterface[] $collections
     * @return InventoryErrorInterface[]
     */
    public function validateCollectionCriteriaRequests(array $collections);

    /**
     * In case if request does not satisfy minimum and maximum sale quantity or incremental options of item,
     * then a new request is returned, otherwise the same request is going to be used.
     *
     * @param InventoryRecordCollectionInterface $collection
     * @return InventoryRecordCriteriaInterface
     */
    public function suggestCollectionCriteria(InventoryRecordCollectionInterface $collection);
}
