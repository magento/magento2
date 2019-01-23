<?php

declare(strict_types=1);

namespace Chizhov\Status\Plugin\Customer\Model;

use Magento\Customer\Model\Customer\DataProvider;
use Chizhov\Status\Api\CustomerStatusRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class CustomerDataProviderPlugin
{
    /**
     * @var \Chizhov\Status\Api\CustomerStatusRepositoryInterface
     */
    protected $statusRepository;

    /**
     * CustomerDataProviderPlugin constructor.
     *
     * @param \Chizhov\Status\Api\CustomerStatusRepositoryInterface $statusRepository
     */
    public function __construct(CustomerStatusRepositoryInterface $statusRepository)
    {
        $this->statusRepository = $statusRepository;
    }

    /**
     * Provide customer status to Customer Form Data Provider.
     *
     * @param \Magento\Customer\Model\Customer\DataProvider $subject
     * @param $data
     * @return mixed
     */
    public function afterGetData(DataProvider $subject, $data)
    {
        if (empty($data)) {
            return $data;
        }

        foreach ($data as $key => $formData) {
            try {
                $customerId = (int)$formData['customer']['entity_id'];

                $customerStatus = $this->statusRepository
                    ->get($customerId)
                    ->getCustomerStatus();

                $data[$key]['customer']['extension_attributes']['chizhov_customer_status'] = $customerStatus;
            } catch (NoSuchEntityException $nsee) {
                continue;
            }
        }

        return $data;
    }
}
