<?php

declare(strict_types=1);

namespace Chizhov\Status\Plugin\Customer\Model;

use Chizhov\Status\Api\CustomerStatusRepositoryInterface;
use Chizhov\Status\Api\Data\CustomerStatusInterface;
use Chizhov\Status\Api\Data\CustomerStatusInterfaceFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerExtensionInterface;
use Magento\Customer\Api\Data\CustomerExtensionInterfaceFactory;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;

/**
 * I didn't realize all necessary methods, but it's enough for the test task.
 */
class CustomerRepositoryPlugin
{
    /**
     * @var \Magento\Customer\Api\Data\CustomerExtensionInterfaceFactory
     */
    protected $extensionFactory;

    /**
     * @var \Chizhov\Status\Api\CustomerStatusRepositoryInterface
     */
    protected $statusRepository;

    /**
     * @var \Chizhov\Status\Api\Data\CustomerStatusInterfaceFactory
     */
    protected $statusFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * CustomerRepositoryPlugin constructor.
     *
     * @param \Chizhov\Status\Api\CustomerStatusRepositoryInterface $statusRepository
     * @param \Magento\Customer\Api\Data\CustomerExtensionInterfaceFactory $extensionFactory
     * @param \Chizhov\Status\Api\Data\CustomerStatusInterfaceFactory $statusFactory
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        CustomerStatusRepositoryInterface $statusRepository,
        CustomerExtensionInterfaceFactory $extensionFactory,
        CustomerStatusInterfaceFactory $statusFactory,
        LoggerInterface $logger
    ) {
        $this->statusRepository = $statusRepository;
        $this->extensionFactory = $extensionFactory;
        $this->statusFactory = $statusFactory;
        $this->logger = $logger;
    }

    /**
     * Set customer status extension attr.
     *
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $subject
     * @param \Magento\Customer\Api\Data\CustomerInterface $result
     * @return \Magento\Customer\Api\Data\CustomerInterface
     */
    public function afterGet(CustomerRepositoryInterface $subject, CustomerInterface $result): CustomerInterface
    {
        return $this->afterGetById($subject, $result);
    }

    /**
     * Set customer status extension attr.
     *
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $subject
     * @param \Magento\Customer\Api\Data\CustomerInterface $result
     * @return \Magento\Customer\Api\Data\CustomerInterface
     */
    public function afterGetById(CustomerRepositoryInterface $subject, CustomerInterface $result): CustomerInterface
    {
        $extensionAttributes = $this->getExtensionAttributes($result);

        try {
            $customerStatus = $this->statusRepository->get((int)$result->getId());
            $extensionAttributes->setChizhovCustomerStatus($customerStatus->getCustomerStatus());
        } catch (NoSuchEntityException $nsee) {
            $extensionAttributes->setChizhovCustomerStatus(null);
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }

        return $result;
    }

    /**
     * Save customer status after save customer.
     *
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $subject
     * @param \Closure $proceed
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @param null $passwordHash
     * @return \Magento\Customer\Api\Data\CustomerInterface
     */
    public function aroundSave(
        CustomerRepositoryInterface $subject,
        \Closure $proceed,
        CustomerInterface $customer,
        $passwordHash = null
    ): CustomerInterface {
        $extensionAttributes = $customer->getExtensionAttributes();
        $customerStatus = $extensionAttributes->getChizhovCustomerStatus();

        $result = $proceed($customer, $passwordHash);

        if ($customerStatus) {
            $status = $this->getCustomerStatusModel((int)$result->getId());
            $status->setCustomerStatus($customerStatus);

            try {
                $this->statusRepository->save($status);
            } catch (\Exception $e) {
                $this->logger->critical($e->getMessage() . PHP_EOL . $e->getTraceAsString());
            }
        } else {
            try {
                $this->statusRepository->deleteById((int)$result->getId());
            } catch (\Exception $exception) {
                $this->logger->critical($exception->getMessage() . PHP_EOL . $exception->getTraceAsString());
            }
        }

        return $result;
    }

    /**
     * Get a CustomerExtensionInterface object, creating it if it is not yet created.
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @return \Magento\Customer\Api\Data\CustomerExtensionInterface
     */
    private function getExtensionAttributes(CustomerInterface $customer): CustomerExtensionInterface
    {
        $extensionAttributes = $customer->getExtensionAttributes();

        if (!$extensionAttributes) {
            $extensionAttributes = $this->extensionFactory->create();
            $customer->setExtensionAttributes($extensionAttributes);
        }

        return $extensionAttributes;
    }

    /**
     * Get Customer Status Model. If there is no in db, create new one.
     *
     * @param int $customerId
     * @return \Chizhov\Status\Api\Data\CustomerStatusInterface
     */
    private function getCustomerStatusModel(int $customerId): CustomerStatusInterface
    {
        try {
            $customerStatus = $this->statusRepository->get($customerId);
        } catch (NoSuchEntityException $nsee) {
            /** @var \Chizhov\Status\Api\Data\CustomerStatusInterface $customerStatus */
            $customerStatus = $this->statusFactory->create();
            $customerStatus->setCustomerId($customerId);
        }

        return $customerStatus;
    }
}
