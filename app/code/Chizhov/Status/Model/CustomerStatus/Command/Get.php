<?php

declare(strict_types=1);

namespace Chizhov\Status\Model\CustomerStatus\Command;

use Chizhov\Status\Api\Data\CustomerStatusInterface;
use Chizhov\Status\Api\Data\CustomerStatusInterfaceFactory;
use Chizhov\Status\Model\ResourceModel\CustomerStatus as CustomerStatusResource;
use Magento\Framework\Exception\NoSuchEntityException;

class Get implements GetInterface
{
    /**
     * @var \Chizhov\Status\Model\ResourceModel\CustomerStatus
     */
    protected $customerStatusResource;

    /**
     * @var \Chizhov\Status\Api\Data\CustomerStatusInterfaceFactory
     */
    protected $customerStatusFactory;

    /**
     * DeleteById constructor.
     *
     * @param \Chizhov\Status\Model\ResourceModel\CustomerStatus $customerStatusResource
     * @param \Chizhov\Status\Api\Data\CustomerStatusInterfaceFactory $customerStatusFactory
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        CustomerStatusResource $customerStatusResource,
        CustomerStatusInterfaceFactory $customerStatusFactory
    ) {
        $this->customerStatusResource = $customerStatusResource;
        $this->customerStatusFactory = $customerStatusFactory;
    }

    /**
     * @inheritDoc
     */
    public function execute(int $customerId): CustomerStatusInterface
    {
        /** @var \Chizhov\Status\Model\CustomerStatus $customerStatus */
        $customerStatus = $this->customerStatusFactory->create();
        $this->customerStatusResource->load($customerStatus, $customerId, CustomerStatusInterface::CUSTOMER_ID);

        if ($customerStatus->getCustomerId() === null) {
            throw new NoSuchEntityException(
                __(
                    "Customer status with %value for %field doesn't exist.",
                    ['value' => $customerId, 'field' => CustomerStatusInterface::CUSTOMER_ID]
                )
            );
        }

        return $customerStatus;
    }
}
