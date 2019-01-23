<?php

declare(strict_types=1);

namespace Chizhov\Status\Model\CustomerStatus\Command;

use Chizhov\Status\Api\Data\CustomerStatusInterface;

/**
 * @see \Magento\InventoryApi\Api\StockRepositoryInterface
 * @api
 */
interface SaveInterface
{
    /**
     * Save customer status.
     *
     * @param \Chizhov\Status\Api\Data\CustomerStatusInterface $status
     * @return int
     * @throws \Magento\Framework\Validation\ValidationException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function execute(CustomerStatusInterface $status): int;
}
