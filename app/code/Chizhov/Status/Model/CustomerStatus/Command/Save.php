<?php

declare(strict_types=1);

namespace Chizhov\Status\Model\CustomerStatus\Command;

use Chizhov\Status\Api\Data\CustomerStatusInterface;
use Chizhov\Status\Model\ResourceModel\CustomerStatus as CustomerStatusResource;
use Magento\Framework\Exception\CouldNotSaveException;
use Psr\Log\LoggerInterface;

class Save implements SaveInterface
{
    /**
     * @var \Chizhov\Status\Model\ResourceModel\CustomerStatus
     */
    protected $customerStatusResource;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * DeleteById constructor.
     *
     * @param \Chizhov\Status\Model\ResourceModel\CustomerStatus $customerStatusResource
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        CustomerStatusResource $customerStatusResource,
        LoggerInterface $logger
    ) {
        $this->customerStatusResource = $customerStatusResource;
        $this->logger = $logger;
    }

    /**
     * Save customer status.
     *
     * @param \Chizhov\Status\Api\Data\CustomerStatusInterface|\Chizhov\Status\Model\CustomerStatus $status
     * @return int
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function execute(CustomerStatusInterface $status): int
    {
        try {
            $this->customerStatusResource->save($status);

            return (int)$status->getCustomerId();
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());

            throw new CouldNotSaveException(__("Couldn't save customer status."), $e);
        }
    }
}
