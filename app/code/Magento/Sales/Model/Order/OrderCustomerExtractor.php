<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Sales\Model\Order;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\DataObject\Copy as CopyService;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\RegionInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory as AddressFactory;
use Magento\Customer\Api\Data\RegionInterfaceFactory as RegionFactory;
use Magento\Customer\Api\Data\CustomerInterfaceFactory as CustomerFactory;
use Magento\Quote\Api\Data\AddressInterfaceFactory as QuoteAddressFactory;
use Magento\Sales\Model\Order\Address as OrderAddress;

/**
 * Extract customer data from an order.
 */
class OrderCustomerExtractor
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var CopyService
     */
    private $objectCopyService;

    /**
     * @var AddressFactory
     */
    private $addressFactory;

    /**
     * @var RegionFactory
     */
    private $regionFactory;

    /**
     * @var CustomerFactory
     */
    private $customerFactory;

    /**
     * @var QuoteAddressFactory
     */
    private $quoteAddressFactory;

    /**
     * @param OrderRepositoryInterface $orderRepository
     * @param CustomerRepositoryInterface $customerRepository
     * @param CopyService $objectCopyService
     * @param AddressFactory $addressFactory
     * @param RegionFactory $regionFactory
     * @param CustomerFactory $customerFactory
     * @param QuoteAddressFactory $quoteAddressFactory
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        CustomerRepositoryInterface $customerRepository,
        CopyService $objectCopyService,
        AddressFactory $addressFactory,
        RegionFactory $regionFactory,
        CustomerFactory $customerFactory,
        QuoteAddressFactory $quoteAddressFactory
    ) {
        $this->orderRepository = $orderRepository;
        $this->customerRepository = $customerRepository;
        $this->objectCopyService = $objectCopyService;
        $this->addressFactory = $addressFactory;
        $this->regionFactory = $regionFactory;
        $this->customerFactory = $customerFactory;
        $this->quoteAddressFactory = $quoteAddressFactory;
    }

    /**
     * Extract customer data from order.
     *
     * @param int $orderId
     * @return CustomerInterface
     */
    public function extract(int $orderId): CustomerInterface
    {
        $order = $this->orderRepository->get($orderId);

        //Simply return customer from DB.
        if ($order->getCustomerId()) {
            return $this->customerRepository->getById($order->getCustomerId());
        }

        //Prepare customer data from order data if customer doesn't exist yet.
        $customerData = $this->objectCopyService->copyFieldsetToTarget(
            'order_address',
            'to_customer',
            $order->getBillingAddress(),
            []
        );
        $addresses = $order->getAddresses();
        $customerAddresses = [];
        // Filter duplicates in order addresses
        foreach ($addresses as $address) {
            $this->addAddress($customerAddresses, $address);
        }
        // Add filtered addresses to customer data
        foreach ($customerAddresses as $addressItem) {
            $customerData['addresses'][] = $addressItem['address'];
        }

        return $this->customerFactory->create(['data' => $customerData]);
    }

    /**
     * Add address to list filtering duplicates.
     *
     * @param array $customerAddresses
     * @param OrderAddress $address
     * @return void
     */
    private function addAddress(
        array &$customerAddresses,
        OrderAddress $address
    ) {
        $addressData = $this->objectCopyService->copyFieldsetToTarget(
            'order_address',
            'to_customer_address',
            $address,
            []
        );

        $foundAddress = null;
        foreach ($customerAddresses as $customerAddressItem) {
            if ($this->isAddressesAreEqual($customerAddressItem['addressData'], $addressData)) {
                $foundAddress = $customerAddressItem['address'];
                break;
            }
        }

        if (empty($foundAddress)) {
            /** @var AddressInterface $customerAddress */
            $customerAddress = $this->addressFactory->create(['data' => $addressData]);

            if (is_string($address->getRegion())) {
                /** @var RegionInterface $region */
                $region = $this->regionFactory->create();
                $region->setRegion($address->getRegion());
                $region->setRegionCode($address->getRegionCode());
                $region->setRegionId($address->getRegionId());
                $customerAddress->setRegion($region);
            }

            $customerAddresses[] = [
                'addressData' => $addressData,
                'address' => $customerAddress,
            ];
            $foundAddress = $customerAddress;
        }

        switch ($address->getAddressType()) {
            case OrderAddress::TYPE_BILLING:
                $foundAddress->setIsDefaultBilling(true);
                break;
            case OrderAddress::TYPE_SHIPPING:
                $foundAddress->setIsDefaultShipping(true);
                break;
        }
    }

    /**
     * Checks if addresses are equal.
     *
     * @param array $addressData1
     * @param array $addressData2
     * @return bool
     */
    private function isAddressesAreEqual(array $addressData1, array $addressData2)
    {
        return $addressData1 == $addressData2;
    }
}
