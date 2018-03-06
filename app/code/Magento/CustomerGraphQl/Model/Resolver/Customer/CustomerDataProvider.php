<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CustomerGraphQl\Model\Resolver\Customer;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Webapi\ServiceOutputProcessor;

/**
 * Customer field data provider, used for GraphQL request processing.
 */
class CustomerDataProvider
{
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var ServiceOutputProcessor
     */
    private $serviceOutputProcessor;

    /**
     * @var SerializerInterface
     */
    private $jsonSerializer;

    /**
     * @param CustomerRepositoryInterface $customerRepository
     * @param ServiceOutputProcessor $serviceOutputProcessor
     * @param SerializerInterface $jsonSerializer
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        ServiceOutputProcessor $serviceOutputProcessor,
        SerializerInterface $jsonSerializer
    ) {
        $this->customerRepository = $customerRepository;
        $this->serviceOutputProcessor = $serviceOutputProcessor;
        $this->jsonSerializer = $jsonSerializer;
    }

    /**
     * Get customer data by Id
     *
     * @param int $customerId
     * @return array|null
     * @throws NoSuchEntityException|LocalizedException
     */
    public function getCustomerById(int $customerId)
    {
        try {
            $customerObject = $this->customerRepository->getById($customerId);
        } catch (NoSuchEntityException $e) {
            // No error should be thrown, null result should be returned
            return null;
        }
        return $this->processCustomer($customerObject);
    }

    /**
     * Transform single customer data from object to in array format
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customerObject
     * @return array|null
     */
    private function processCustomer(\Magento\Customer\Api\Data\CustomerInterface $customerObject)
    {
        $customer = $this->serviceOutputProcessor->process(
            $customerObject,
            CustomerRepositoryInterface::class,
            'get'
        );
        if (isset($customer['extension_attributes'])) {
            $customer = array_merge($customer, $customer['extension_attributes']);
        }
        $customAttributes = [];
        if (isset($customer['custom_attributes'])) {
            foreach ($customer['custom_attributes'] as $attribute) {
                $isArray = false;
                if (is_array($attribute['value'])) {
                    $isArray = true;
                    foreach ($attribute['value'] as $attributeValue) {
                        if (is_array($attributeValue)) {
                            $customAttributes[$attribute['attribute_code']] = $this->jsonSerializer->serialize(
                                $attribute['value']
                            );
                            continue;
                        }
                        $customAttributes[$attribute['attribute_code']] = implode(',', $attribute['value']);
                        continue;
                    }
                }
                if ($isArray) {
                    continue;
                }
                $customAttributes[$attribute['attribute_code']] = $attribute['value'];
            }
        }
        $customer = array_merge($customer, $customAttributes);

        return $customer;
    }
}
