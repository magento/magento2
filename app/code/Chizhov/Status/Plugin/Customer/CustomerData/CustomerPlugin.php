<?php

declare(strict_types=1);

namespace Chizhov\Status\Plugin\Customer\CustomerData;

use Chizhov\Status\Api\CustomerStatusRepositoryInterface;
use Magento\Customer\CustomerData\Customer;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Framework\Exception\NoSuchEntityException;

class CustomerPlugin
{
    /**
     * @var \Magento\Customer\Helper\Session\CurrentCustomer
     */
    protected $currentCustomer;

    /**
     * @var \Chizhov\Status\Api\CustomerStatusRepositoryInterface
     */
    protected $statusRepository;

    /**
     * CustomerPlugin constructor.
     *
     * @param \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer
     * @param \Chizhov\Status\Api\CustomerStatusRepositoryInterface $statusRepository
     */
    public function __construct(CurrentCustomer $currentCustomer, CustomerStatusRepositoryInterface $statusRepository)
    {
        $this->currentCustomer = $currentCustomer;
        $this->statusRepository = $statusRepository;
    }

    /**
     * Provide customer status to Customer Data (used in header).
     *
     * @param \Magento\Customer\CustomerData\Customer $subject
     * @param array $result
     * @return array
     */
    public function afterGetSectionData(Customer $subject, array $result): array
    {
        if ($this->currentCustomer->getCustomerId()) {
            try {
                $customerId = (int)$this->currentCustomer->getCustomerId();
                $customerStatus = $this->statusRepository->get($customerId)->getCustomerStatus();
            } catch (NoSuchEntityException $nsee) {
                $customerStatus = null;
            }

            $result['customer_status'] = $customerStatus;
        }

        return $result;
    }
}
