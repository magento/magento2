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
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Framework\Encryption\EncryptorInterface as Encryptor;
use Magento\Store\Api\StoreResolverInterface;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;

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
     * @var StoreResolverInterface
     */
    private $storeResolver;

    /**
     * @var \Magento\Newsletter\Model\SubscriberFactory
     */
    protected $subscriberFactory;

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
     * @param CustomerRepositoryInterface $customerRepository
     * @param ServiceOutputProcessor $serviceOutputProcessor
     * @param SerializerInterface $jsonSerializer
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        ServiceOutputProcessor $serviceOutputProcessor,
        SerializerInterface $jsonSerializer,
        SubscriberFactory $subscriberFactory,
        CustomerRegistry $customerRegistry,
        Encryptor $encryptor,
        StoreResolverInterface $storeResolver
    ) {
        $this->customerRepository = $customerRepository;
        $this->serviceOutputProcessor = $serviceOutputProcessor;
        $this->jsonSerializer = $jsonSerializer;
        $this->subscriberFactory = $subscriberFactory;
        $this->customerRegistry = $customerRegistry;
        $this->encryptor = $encryptor;
        $this->storeResolver = $storeResolver;
    }

    /**
     * Load customer object
     *
     * @param int $customerId
     * @return CustomerInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function loadCustomerById(int $customerId): CustomerInterface
    {
        return $this->customerRepository->getById($customerId);
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
     * Check if customer is subscribed to Newsletter
     *
     * @param int $customerId
     * @return bool
     */
    public function isSubscribed(int $customerId): bool
    {
        $checkSubscriber = $this->subscriberFactory->create()->loadByCustomerId($customerId);
        return $checkSubscriber->isSubscribed();
    }

    /**
     * Manage customer subscription. Subscribe OR unsubscribe if required
     *
     * @param int $customerId
     * @param $newSubscriptionStatus
     * @return bool
     */
    public function manageSubscription(int $customerId, bool $newSubscriptionStatus): bool
    {
        $checkSubscriber = $this->subscriberFactory->create()->loadByCustomerId($customerId);
        $isSubscribed = $this->isSubscribed($customerId);

        if ($newSubscriptionStatus === true && !$isSubscribed) {
            $this->subscriberFactory->create()->subscribeCustomerById($customerId);
        } elseif ($newSubscriptionStatus === false && $checkSubscriber->isSubscribed()) {
            $this->subscriberFactory->create()->unsubscribeCustomerById($customerId);
        }
        return true;
    }

    /**
     * @param int $customerId
     * @param array $customerData
     * @return CustomerInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\State\InputMismatchException
     */
    public function updateAccountInformation(int $customerId, array $customerData): CustomerInterface
    {

        $customer = $this->loadCustomerById($customerId);

        if (isset($customerData['email'])
            && $customer->getEmail() !== $customerData['email']
            && isset($customerData['password'])) {
            if ($this->isPasswordCorrect($customerData['password'], $customerId)) {
                $customer->setEmail($customerData['email']);
            } else {
                throw new GraphQlAuthorizationException(__('Invalid current user password.'));
            }
        }

        if (isset($customerData['firstname'])) {
            $customer->setFirstname($customerData['firstname']);
        }
        if (isset($customerData['lastname'])) {
            $customer->setLastname($customerData['lastname']);
        }

        $customer->setStoreId($this->storeResolver->getCurrentStoreId());
        $this->customerRepository->save($customer);

        return $customer;
    }

    private function isPasswordCorrect(string $password, int $customerId)
    {

        $customerSecure = $this->customerRegistry->retrieveSecureData($customerId);
        $hash = $customerSecure->getPasswordHash();
        if (!$this->encryptor->validateHash($password, $hash)) {
            return false;
        }
        return true;
    }
}
