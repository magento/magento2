<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\Order;

use Magento\Customer\Api\Data\AddressInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Quote\Model\Quote\Address as QuoteAddress;
use Magento\Quote\Model\Quote\AddressFactory as QuoteAddressFactory;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Class CustomerManagement
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CustomerManagement implements \Magento\Sales\Api\OrderCustomerManagementInterface
{
    /**
     * @var \Magento\Customer\Api\AccountManagementInterface
     */
    protected $accountManagement;

    /**
     * @deprecated 101.0.4
     */
    protected $customerFactory;

    /**
     * @deprecated 101.0.4
     */
    protected $addressFactory;

    /**
     * @deprecated 101.0.4
     */
    protected $regionFactory;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @deprecated 101.0.4
     */
    protected $objectCopyService;

    /**
     * @var QuoteAddressFactory
     */
    private $quoteAddressFactory;

    /**
     * @var OrderCustomerExtractor
     */
    private $customerExtractor;

    /**
     * @param \Magento\Framework\DataObject\Copy $objectCopyService
     * @param \Magento\Customer\Api\AccountManagementInterface $accountManagement
     * @param \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerFactory
     * @param \Magento\Customer\Api\Data\AddressInterfaceFactory $addressFactory
     * @param \Magento\Customer\Api\Data\RegionInterfaceFactory $regionFactory
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param QuoteAddressFactory|null $quoteAddressFactory
     * @param OrderCustomerExtractor|null $orderCustomerExtractor
     */
    public function __construct(
        \Magento\Framework\DataObject\Copy $objectCopyService,
        \Magento\Customer\Api\AccountManagementInterface $accountManagement,
        \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerFactory,
        \Magento\Customer\Api\Data\AddressInterfaceFactory $addressFactory,
        \Magento\Customer\Api\Data\RegionInterfaceFactory $regionFactory,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        ?QuoteAddressFactory $quoteAddressFactory = null,
        ?OrderCustomerExtractor $orderCustomerExtractor = null
    ) {
        $this->objectCopyService = $objectCopyService;
        $this->accountManagement = $accountManagement;
        $this->orderRepository = $orderRepository;
        $this->customerFactory = $customerFactory;
        $this->addressFactory = $addressFactory;
        $this->regionFactory = $regionFactory;
        $this->quoteAddressFactory = $quoteAddressFactory
            ?: ObjectManager::getInstance()->get(QuoteAddressFactory::class);
        $this->customerExtractor = $orderCustomerExtractor
            ?? ObjectManager::getInstance()->get(OrderCustomerExtractor::class);
    }

    /**
     * {@inheritdoc}
     */
    public function create($orderId)
    {
        $order = $this->orderRepository->get($orderId);
        if ($order->getCustomerId()) {
            throw new AlreadyExistsException(
                __('This order already has associated customer account')
            );
        }

        $customer = $this->customerExtractor->extract($orderId);
        /** @var AddressInterface[] $filteredAddresses */
        $filteredAddresses = [];
        foreach ($customer->getAddresses() as $address) {
            if ($this->needToSaveAddress($order, $address)) {
                $filteredAddresses[] = $address;
            }
        }
        $customer->setAddresses($filteredAddresses);

        $account = $this->accountManagement->createAccount($customer);
        $order = $this->orderRepository->get($orderId);
        $order->setCustomerId($account->getId());
        $order->setCustomerIsGuest(0);
        $this->orderRepository->save($order);

        return $account;
    }

    /**
     * @param OrderInterface $order
     * @param AddressInterface $address
     *
     * @return bool
     */
    private function needToSaveAddress(
        OrderInterface $order,
        AddressInterface $address
    ): bool {
        /** @var OrderAddressInterface|null $orderAddress */
        $orderAddress = null;
        if ($address->isDefaultBilling()) {
            $orderAddress = $order->getBillingAddress();
        } elseif ($address->isDefaultShipping()) {
            $orderAddress = $order->getShippingAddress();
        }
        if ($orderAddress) {
            $quoteAddressId = $orderAddress->getData('quote_address_id');
            if ($quoteAddressId) {
                /** @var QuoteAddress $quote */
                $quote = $this->quoteAddressFactory->create()
                    ->load($quoteAddressId);
                if ($quote && $quote->getId()) {
                    return (bool)(int)$quote->getData('save_in_address_book');
                }
            }

            return true;
        }

        return false;
    }
}
