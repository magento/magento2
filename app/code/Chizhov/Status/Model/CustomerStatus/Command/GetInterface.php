<?php

declare(strict_types=1);

namespace Chizhov\Status\Model\CustomerStatus\Command;

use Chizhov\Status\Api\Data\CustomerStatusInterface;

/**
 * @see \Magento\InventoryApi\Api\StockRepositoryInterface
 * @api
 */
interface GetInterface
{
    /**
     * Get customer status by given customer ID.
     *
     * @param int $customerId
     * @return \Chizhov\Status\Api\Data\CustomerStatusInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(int $customerId): CustomerStatusInterface;
}
