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
     * @param InventoryRequestInterface[] $requests
     * @param InventoryRecordInterface[][] $response
     * @return InventoryErrorInterface[]
     */
    public function validateRequests(array $requests, $response);

    /**
     * In case if request does not satisfy minimum and maximum sale quantity or incremental options of item,
     * then a new request is returned, otherwise the same request is going to be used.
     *
     * @param InventoryRecordInterface $inventoryRecord
     * @param RequestInterface|null $currentRequest
     * @return RequestInterface
     */
    public function suggestRequest(InventoryRecordInterface $inventoryRecord, RequestInterface $currentRequest = null);
}
