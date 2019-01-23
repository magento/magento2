<?php

namespace Chizhov\Status\ViewModel\Customer;

use Chizhov\Status\Api\CustomerStatusRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class Status implements ArgumentInterface
{
    /**
     * @var \Chizhov\Status\Api\CustomerStatusRepositoryInterface
     */
    protected $statusRepository;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * Status constructor.
     *
     * @param \Chizhov\Status\Api\CustomerStatusRepositoryInterface $statusRepository
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(CustomerStatusRepositoryInterface $statusRepository, Session $customerSession)
    {
        $this->statusRepository = $statusRepository;
        $this->customerSession = $customerSession;
    }

    /**
     * Get the current customer status.
     *
     * @return string|null
     */
    public function getCustomerStatus(): ?string
    {
        try {
            $customerId = (int)$this->customerSession->getId();

            $customerStatus = $this->statusRepository->get($customerId)->getCustomerStatus();
        } catch (NoSuchEntityException $nsee) {
            $customerStatus = null;
        }

        return $customerStatus;
    }
}
