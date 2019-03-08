<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\AddressRegistry;
use Magento\Customer\Model\EmailNotificationInterface;
use Magento\Customer\Ui\Component\Listing\AttributeRepository;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\MessageInterface;
use Magento\Framework\App\ObjectManager;

/**
 * Customer inline edit action
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InlineEdit extends \Magento\Backend\App\Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Customer::manage';

    /**
     * @var \Magento\Customer\Api\Data\CustomerInterface
     */
    private $customer;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Customer\Model\Customer\Mapper
     */
    protected $customerMapper;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Customer\Model\EmailNotificationInterface
     */
    private $emailNotification;

    /**
     * @var AddressRegistry
     */
    private $addressRegistry;

    /**
     * @param Action\Context $context
     * @param CustomerRepositoryInterface $customerRepository
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Customer\Model\Customer\Mapper $customerMapper
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param \Psr\Log\LoggerInterface $logger
     * @param AddressRegistry|null $addressRegistry
     */
    public function __construct(
        Action\Context $context,
        CustomerRepositoryInterface $customerRepository,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Customer\Model\Customer\Mapper $customerMapper,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Psr\Log\LoggerInterface $logger,
        AddressRegistry $addressRegistry = null
    ) {
        $this->customerRepository = $customerRepository;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->customerMapper = $customerMapper;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->logger = $logger;
        $this->addressRegistry = $addressRegistry ?: ObjectManager::getInstance()->get(AddressRegistry::class);
        parent::__construct($context);
    }

    /**
     * Get email notification
     *
     * @return EmailNotificationInterface
     * @deprecated 100.1.0
     */
    private function getEmailNotification()
    {
        if (!($this->emailNotification instanceof EmailNotificationInterface)) {
            return \Magento\Framework\App\ObjectManager::getInstance()->get(
                EmailNotificationInterface::class
            );
        } else {
            return $this->emailNotification;
        }
    }

    /**
     * Inline edit action execute
     *
     * @return \Magento\Framework\Controller\Result\Json
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();

        $postItems = $this->getRequest()->getParam('items', []);
        if (!($this->getRequest()->getParam('isAjax') && count($postItems))) {
            return $resultJson->setData([
                'messages' => [__('Please correct the data sent.')],
                'error' => true,
            ]);
        }

        foreach (array_keys($postItems) as $customerId) {
            $this->setCustomer($this->customerRepository->getById($customerId));
            $currentCustomer = clone $this->getCustomer();

            if ($this->getCustomer()->getDefaultBilling()) {
                $this->updateDefaultBilling($this->getData($postItems[$customerId]));
            }
            $this->updateCustomer($this->getData($postItems[$customerId], true));
            $this->saveCustomer($this->getCustomer());

            $this->getEmailNotification()->credentialsChanged($this->getCustomer(), $currentCustomer->getEmail());
        }

        return $resultJson->setData([
            'messages' => $this->getErrorMessages(),
            'error' => $this->isErrorExists()
        ]);
    }

    /**
     * Receive entity(customer|customer_address) data from request
     *
     * @param array $data
     * @param mixed $isCustomerData
     * @return array
     */
    protected function getData(array $data, $isCustomerData = null)
    {
        $addressKeys = preg_grep(
            '/^(' . AttributeRepository::BILLING_ADDRESS_PREFIX . '\w+)/',
            array_keys($data),
            $isCustomerData
        );
        $result = array_intersect_key($data, array_flip($addressKeys));
        if ($isCustomerData === null) {
            foreach ($result as $key => $value) {
                if (strpos($key, AttributeRepository::BILLING_ADDRESS_PREFIX) !== false) {
                    unset($result[$key]);
                    $result[str_replace(AttributeRepository::BILLING_ADDRESS_PREFIX, '', $key)] = $value;
                }
            }
        }
        return $result;
    }

    /**
     * Update customer data
     *
     * @param array $data
     * @return void
     */
    protected function updateCustomer(array $data)
    {
        $customer = $this->getCustomer();
        $customerData = array_merge(
            $this->customerMapper->toFlatArray($customer),
            $data
        );
        $this->dataObjectHelper->populateWithArray(
            $customer,
            $customerData,
            \Magento\Customer\Api\Data\CustomerInterface::class
        );
    }

    /**
     * Update customer address data
     *
     * @param array $data
     * @return void
     */
    protected function updateDefaultBilling(array $data)
    {
        $addresses = $this->getCustomer()->getAddresses();
        /** @var \Magento\Customer\Api\Data\AddressInterface $address */
        foreach ($addresses as $address) {
            if ($address->isDefaultBilling()) {
                $this->dataObjectHelper->populateWithArray(
                    $address,
                    $this->processAddressData($data),
                    \Magento\Customer\Api\Data\AddressInterface::class
                );
                break;
            }
        }
    }

    /**
     * Save customer with error catching
     *
     * @param CustomerInterface $customer
     * @return void
     */
    protected function saveCustomer(CustomerInterface $customer)
    {
        try {
            // No need to validate customer address during inline edit action
            $this->disableAddressValidation($customer);
            $this->customerRepository->save($customer);
        } catch (\Magento\Framework\Exception\InputException $e) {
            $this->getMessageManager()->addError($this->getErrorWithCustomerId($e->getMessage()));
            $this->logger->critical($e);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->getMessageManager()->addError($this->getErrorWithCustomerId($e->getMessage()));
            $this->logger->critical($e);
        } catch (\Exception $e) {
            $this->getMessageManager()->addError($this->getErrorWithCustomerId('We can\'t save the customer.'));
            $this->logger->critical($e);
        }
    }

    /**
     * Parse street field
     *
     * @param array $data
     * @return array
     */
    protected function processAddressData(array $data)
    {
        foreach (['firstname', 'lastname'] as $requiredField) {
            if (empty($data[$requiredField])) {
                $data[$requiredField] =  $this->getCustomer()->{'get' . ucfirst($requiredField)}();
            }
        }
        return $data;
    }

    /**
     * Get array with errors
     *
     * @return array
     */
    protected function getErrorMessages()
    {
        $messages = [];
        foreach ($this->getMessageManager()->getMessages()->getErrors() as $error) {
            $messages[] = $error->getText();
        }
        return $messages;
    }

    /**
     * Check if errors exists
     *
     * @return bool
     */
    protected function isErrorExists()
    {
        return (bool)$this->getMessageManager()->getMessages(true)->getCountByType(MessageInterface::TYPE_ERROR);
    }

    /**
     * Set customer
     *
     * @param CustomerInterface $customer
     * @return $this
     */
    protected function setCustomer(CustomerInterface $customer)
    {
        $this->customer = $customer;
        return $this;
    }

    /**
     * Receive customer
     *
     * @return CustomerInterface
     */
    protected function getCustomer()
    {
        return $this->customer;
    }

    /**
     * Add page title to error message
     *
     * @param string $errorText
     * @return string
     */
    protected function getErrorWithCustomerId($errorText)
    {
        return '[Customer ID: ' . $this->getCustomer()->getId() . '] ' . __($errorText);
    }

    /**
     * Disable Customer Address Validation
     *
     * @param CustomerInterface $customer
     * @throws NoSuchEntityException
     */
    private function disableAddressValidation($customer)
    {
        foreach ($customer->getAddresses() as $address) {
            $addressModel = $this->addressRegistry->retrieve($address->getId());
            $addressModel->setShouldIgnoreValidation(true);
        }
    }
}
