<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\Order;

use Magento\Framework\App\ObjectManager;
use Magento\Sales\Helper\OrderCustomerExtractor;

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
     * @deprecated
     */
    protected $customerFactory;

    /**
     * @deprecated
     */
    protected $addressFactory;

    /**
     * @deprecated
     */
    protected $regionFactory;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @deprecated
     */
    protected $objectCopyService;

    /**
     * @deprecated
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
     * @param \Magento\Quote\Model\Quote\AddressFactory|null $quoteAddressFactory
     * @param OrderCustomerExtractor|null $orderCustomerExtractor
     */
    public function __construct(
        \Magento\Framework\DataObject\Copy $objectCopyService,
        \Magento\Customer\Api\AccountManagementInterface $accountManagement,
        \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerFactory,
        \Magento\Customer\Api\Data\AddressInterfaceFactory $addressFactory,
        \Magento\Customer\Api\Data\RegionInterfaceFactory $regionFactory,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Quote\Model\Quote\AddressFactory $quoteAddressFactory = null,
        OrderCustomerExtractor $orderCustomerExtractor = null
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
        $this->customerExtractor = $orderCustomerExtractor
            ?? ObjectManager::getInstance()->get(OrderCustomerExtractor::class);
    }

    /**
     * {@inheritdoc}
     */
    public function create($orderId)
    {
        $customer = $this->customerExtractor->extract($orderId);
        $account = $this->accountManagement->createAccount($customer);
        $order = $this->orderRepository->get($orderId);
        $order->setCustomerId($account->getId());
        $order->setCustomerIsGuest(0);
        $this->orderRepository->save($order);

        return $account;
    }
}
