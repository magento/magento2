<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryDistanceBasedSourceSelection\Model;

use Magento\InventoryDistanceBasedSourceSelectionApi\Api\Data\AddressInterface;
use Magento\InventoryDistanceBasedSourceSelectionApi\Api\Data\AddressInterfaceFactory;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * Create an address  from an order
 */
class GetAddressFromOrder
{
    /**
     * @var AddressInterfaceFactory
     */
    private $addressInterfaceFactory;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * GetAddressFromOrder constructor
     *
     * @param OrderRepositoryInterface $orderRepository
     * @param AddressInterfaceFactory $addressInterfaceFactory
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        AddressInterfaceFactory $addressInterfaceFactory
    ) {
        $this->addressInterfaceFactory = $addressInterfaceFactory;
        $this->orderRepository = $orderRepository;
    }

    /**
     * Create an address  from an order
     *
     * @param int $orderId
     * @return AddressInterface
     */
    public function execute(int $orderId): AddressInterface
    {
        $order = $this->orderRepository->get($orderId);

        /** @var \Magento\Sales\Model\Order\Address $shippingAddress */
        $shippingAddress = $order->getShippingAddress();

        return $this->addressInterfaceFactory->create([
            'country' => $shippingAddress->getCountryId(),
            'postcode' => $shippingAddress->getPostcode(),
            'streetAddress' => implode("\n", $shippingAddress->getStreet()),
            'region' => $shippingAddress->getRegion() ?? $shippingAddress->getRegionCode() ?? '',
            'city' => $shippingAddress->getCity()
        ]);
    }
}
