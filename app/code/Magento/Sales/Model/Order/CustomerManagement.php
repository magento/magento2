<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\Order;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Quote\Model\Quote\Address as QuoteAddress;

/**
 * Class CustomerManagement
 */
class CustomerManagement implements \Magento\Sales\Api\OrderCustomerManagementInterface
{
    /**
     * @var \Magento\Customer\Api\AccountManagementInterface
     */
    protected $accountManagement;

    /**
     * @var \Magento\Customer\Api\Data\CustomerInterfaceFactory
     */
    protected $customerFactory;

    /**
     * @var \Magento\Customer\Api\Data\AddressInterfaceFactory
     */
    protected $addressFactory;

    /**
     * @var \Magento\Customer\Api\Data\RegionInterfaceFactory
     */
    protected $regionFactory;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var \Magento\Framework\DataObject\Copy
     */
    protected $objectCopyService;

    /**
     * @var \Magento\Quote\Model\Quote\AddressFactory
     */
    private $quoteAddressFactory;

    /**
     * @param \Magento\Framework\DataObject\Copy $objectCopyService
     * @param \Magento\Customer\Api\AccountManagementInterface $accountManagement
     * @param \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerFactory
     * @param \Magento\Customer\Api\Data\AddressInterfaceFactory $addressFactory
     * @param \Magento\Customer\Api\Data\RegionInterfaceFactory $regionFactory
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Quote\Model\Quote\AddressFactory|null $quoteAddressFactory
     */
    public function __construct(
        \Magento\Framework\DataObject\Copy $objectCopyService,
        \Magento\Customer\Api\AccountManagementInterface $accountManagement,
        \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerFactory,
        \Magento\Customer\Api\Data\AddressInterfaceFactory $addressFactory,
        \Magento\Customer\Api\Data\RegionInterfaceFactory $regionFactory,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Quote\Model\Quote\AddressFactory $quoteAddressFactory = null
    ) {
        $this->objectCopyService = $objectCopyService;
        $this->accountManagement = $accountManagement;
        $this->orderRepository = $orderRepository;
        $this->customerFactory = $customerFactory;
        $this->addressFactory = $addressFactory;
        $this->regionFactory = $regionFactory;
        $this->quoteAddressFactory = $quoteAddressFactory ?: ObjectManager::getInstance()->get(
            \Magento\Quote\Model\Quote\AddressFactory::class
        );
    }

    /**
     * {@inheritdoc}
     */
    public function create($orderId)
    {
        $order = $this->orderRepository->get($orderId);
        if ($order->getCustomerId()) {
            throw new AlreadyExistsException(__("This order already has associated customer account"));
        }
        $customerData = $this->objectCopyService->copyFieldsetToTarget(
            'order_address',
            'to_customer',
            $order->getBillingAddress(),
            []
        );
        $addresses = $order->getAddresses();
        foreach ($addresses as $address) {
            if (!$this->isNeededToSaveAddress($address->getData('quote_address_id'))) {
                continue;
            }
            $addressData = $this->objectCopyService->copyFieldsetToTarget(
                'order_address',
                'to_customer_address',
                $address,
                []
            );
            /** @var \Magento\Customer\Api\Data\AddressInterface $customerAddress */
            $customerAddress = $this->addressFactory->create(['data' => $addressData]);
            switch ($address->getAddressType()) {
                case QuoteAddress::ADDRESS_TYPE_BILLING:
                    $customerAddress->setIsDefaultBilling(true);
                    break;
                case QuoteAddress::ADDRESS_TYPE_SHIPPING:
                    $customerAddress->setIsDefaultShipping(true);
                    break;
            }

            if (is_string($address->getRegion())) {
                /** @var \Magento\Customer\Api\Data\RegionInterface $region */
                $region = $this->regionFactory->create();
                $region->setRegion($address->getRegion());
                $region->setRegionCode($address->getRegionCode());
                $region->setRegionId($address->getRegionId());
                $customerAddress->setRegion($region);
            }
            $customerData['addresses'][] = $customerAddress;
        }

        /** @var \Magento\Customer\Api\Data\CustomerInterface $customer */
        $customer = $this->customerFactory->create(['data' => $customerData]);
        $account = $this->accountManagement->createAccount($customer);
        $order->setCustomerId($account->getId());
        $this->orderRepository->save($order);

        return $account;
    }

    /**
     * Check if we need to save address in address book.
     *
     * @param int $quoteAddressId
     *
     * @return bool
     */
    private function isNeededToSaveAddress($quoteAddressId)
    {
        $saveInAddressBook = true;

        $quoteAddress = $this->quoteAddressFactory->create()->load($quoteAddressId);
        if ($quoteAddress && $quoteAddress->getId()) {
            $saveInAddressBook = (int)$quoteAddress->getData('save_in_address_book');
        }

        return $saveInAddressBook;
    }
}
