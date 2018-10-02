<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Resolver\Customer;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Webapi\ServiceOutputProcessor;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Framework\Encryption\EncryptorInterface as Encryptor;

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
     * @var CustomerRegistry
     */
    protected $customerRegistry;

    /**
     * @var SerializerInterface
     */
    private $jsonSerializer;

    /**
     * @var Encryptor
     */
    protected $encryptor;

    /**
     * CustomerDataProvider constructor.
     *
     * @param CustomerRepositoryInterface $customerRepository
     * @param ServiceOutputProcessor $serviceOutputProcessor
     * @param SerializerInterface $jsonSerializer
     * @param CustomerRegistry $customerRegistry
     * @param Encryptor $encryptor
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        ServiceOutputProcessor $serviceOutputProcessor,
        SerializerInterface $jsonSerializer,
        CustomerRegistry $customerRegistry,
        Encryptor $encryptor
    ) {
        $this->customerRepository = $customerRepository;
        $this->serviceOutputProcessor = $serviceOutputProcessor;
        $this->jsonSerializer = $jsonSerializer;
        $this->customerRegistry = $customerRegistry;
        $this->encryptor = $encryptor;
    }

    /**
     * Get customer data by Id or empty array
     *
     * @param int $customerId
     * @return array
     * @throws NoSuchEntityException|LocalizedException
     */
    public function getCustomerById(int $customerId): array
    {
        try {
            $customerObject = $this->customerRepository->getById($customerId);
        } catch (NoSuchEntityException $e) {
            // No error should be thrown, null result should be returned
            return [];
        }
        return $this->processCustomer($customerObject);
    }

    /**
     * Transform single customer data from object to in array format
     *
     * @param CustomerInterface $customerObject
     * @return array
     */
    private function processCustomer(CustomerInterface $customerObject): array
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

    /**
     * Checking if password for customer is correct
     *
     * @param string $password
     * @param int $customerId
     * @return bool
     * @throws NoSuchEntityException
     */
    public function isPasswordCorrect(string $password, int $customerId)
    {
        $hash = $this->customerRegistry->retrieveSecureData($customerId)->getPasswordHash();
        return $this->encryptor->validateHash($password, $hash);
    }
}
