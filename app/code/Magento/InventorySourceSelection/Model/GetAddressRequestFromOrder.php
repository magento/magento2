<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySourceSelection\Model;

use Magento\InventorySourceSelectionApi\Api\Data\AddressRequestInterface;
use Magento\InventorySourceSelectionApi\Api\Data\AddressRequestInterfaceFactory;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * Create an address request from an order
 */
class GetAddressRequestFromOrder
{
    /**
     * @var AddressRequestInterfaceFactory
     */
    private $addressRequestInterfaceFactory;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * GetAddressRequestFromOrder constructor
     *
     * @param OrderRepositoryInterface $orderRepository
     * @param AddressRequestInterfaceFactory $addressRequestInterfaceFactory
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        AddressRequestInterfaceFactory $addressRequestInterfaceFactory
    ) {
        $this->addressRequestInterfaceFactory = $addressRequestInterfaceFactory;
        $this->orderRepository = $orderRepository;
    }

    /**
     * Create an address request from an order
     *
     * @param int $orderId
     * @return AddressRequestInterface
     */
    public function execute(int $orderId): AddressRequestInterface
    {
        $order = $this->orderRepository->get($orderId);

        /** @var \Magento\Sales\Model\Order\Address $shippingAddress */
        $shippingAddress = $order->getShippingAddress();

        return $this->addressRequestInterfaceFactory->create([
            'country' => $shippingAddress->getCountryId(),
            'postcode' => $shippingAddress->getPostcode(),
            'streetAddress' => implode("\n", $shippingAddress->getStreet()),
            'region' => $shippingAddress->getRegion() ?? $shippingAddress->getRegionCode() ?? '',
            'city' => $shippingAddress->getCity()
        ]);
    }
}
