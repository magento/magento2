<?php

declare(strict_types=1);

namespace Chizhov\Status\Model\CustomerStatus\Command;

interface DeleteByIdInterface
{
    /**
     * Delete the customer status data by customer ID.
     *
     * @param int $customerId
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function execute(int $customerId): void;
}
