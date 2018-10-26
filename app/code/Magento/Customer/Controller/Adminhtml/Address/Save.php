<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Adminhtml\Address;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\Redirect;
use Psr\Log\LoggerInterface;

/**
 * Class for saving of customer address
 */
class Save extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Magento_Customer::manage';

    /**
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var \Magento\Customer\Model\Metadata\FormFactory
     */
    private $formFactory;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var \Magento\Customer\Api\Data\AddressInterfaceFactory
     */
    private $addressDataFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Action\Context $context
     * @param \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
     * @param \Magento\Customer\Model\Metadata\FormFactory $formFactory
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param \Magento\Customer\Api\Data\AddressInterfaceFactory $addressDataFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        Action\Context $context,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Customer\Model\Metadata\FormFactory $formFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magento\Customer\Api\Data\AddressInterfaceFactory $addressDataFactory,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->addressRepository = $addressRepository;
        $this->formFactory = $formFactory;
        $this->customerRepository = $customerRepository;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->addressDataFactory = $addressDataFactory;
        $this->logger = $logger;
    }

    /**
     * Execute action to save customer address
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(): Redirect
    {
        $customerId = $this->getRequest()->getParam('parent_id', false);
        $addressId = $this->getRequest()->getParam('entity_id', false);
        /** @var \Magento\Customer\Api\Data\CustomerInterface $customer */
        $customer = $this->customerRepository->getById($customerId);

        try {
            $addressForm = $this->formFactory->create(
                'customer_address',
                'adminhtml_customer_address',
                [],
                false,
                false
            );
            $addressData = $addressForm->extractData($this->getRequest());
            $addressData = $addressForm->compactData($addressData);

            $addressData['region'] = [
                'region' => $addressData['region'] ?? null,
                'region_id' => $addressData['region_id'] ?? null,
            ];
            $addressToSave = $this->addressDataFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $addressToSave,
                $addressData,
                \Magento\Customer\Api\Data\AddressInterface::class
            );
            $addressToSave->setCustomerId($customer->getId());
            $addressToSave->setIsDefaultBilling(
                (bool)$this->getRequest()->getParam('default_billing', false)
            );
            $addressToSave->setIsDefaultShipping(
                (bool)$this->getRequest()->getParam('default_shipping', false)
            );
            if ($addressId) {
                $addressToSave->setId($addressId);
                $saveMessage = __('Customer address has been updated.');
            } else {
                $addressToSave->setId(null);
                $saveMessage = __('New customer address has been added.');
            }

            $this->addressRepository->save($addressToSave);
            $this->messageManager->addSuccessMessage($saveMessage);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->logger->critical($e);
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                __('We can\'t change customer address right now.')
            );
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath(
            'customer/index/edit',
            ['id' => $customerId, '_current' => true]
        );
        return $resultRedirect;
    }
}
