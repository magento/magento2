<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Model\GuestCart;

use Magento\Framework\App\ObjectManager;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\GuestShipmentEstimationInterface;
use Magento\Quote\Api\ShipmentEstimationInterface;
use Magento\Quote\Api\ShippingMethodManagementInterface;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteIdMaskFactory;

/**
 * Shipping method management class for guest carts.
 * @since 2.0.0
 */
class GuestShippingMethodManagement implements
    \Magento\Quote\Api\GuestShippingMethodManagementInterface,
    \Magento\Quote\Model\GuestCart\GuestShippingMethodManagementInterface,
    GuestShipmentEstimationInterface
{
    /**
     * @var ShippingMethodManagementInterface
     * @since 2.0.0
     */
    private $shippingMethodManagement;

    /**
     * @var QuoteIdMaskFactory
     * @since 2.0.0
     */
    private $quoteIdMaskFactory;

    /**
     * @var ShipmentEstimationInterface
     * @since 2.1.0
     */
    private $shipmentEstimationManagement;

    /**
     * Constructs a shipping method read service object.
     *
     * @param ShippingMethodManagementInterface $shippingMethodManagement
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @since 2.0.0
     */
    public function __construct(
        ShippingMethodManagementInterface $shippingMethodManagement,
        QuoteIdMaskFactory $quoteIdMaskFactory
    ) {
        $this->shippingMethodManagement = $shippingMethodManagement;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
    }

    /**
     * {@inheritDoc}
     * @since 2.0.0
     */
    public function get($cartId)
    {
        /** @var $quoteIdMask QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        return $this->shippingMethodManagement->get($quoteIdMask->getQuoteId());
    }

    /**
     * {@inheritDoc}
     * @since 2.0.0
     */
    public function getList($cartId)
    {
        /** @var $quoteIdMask QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        return $this->shippingMethodManagement->getList($quoteIdMask->getQuoteId());
    }

    /**
     * {@inheritDoc}
     * @since 2.0.0
     */
    public function set($cartId, $carrierCode, $methodCode)
    {
        /** @var $quoteIdMask QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        return $this->shippingMethodManagement->set($quoteIdMask->getQuoteId(), $carrierCode, $methodCode);
    }

    /**
     * {@inheritDoc}
     * @since 2.0.0
     */
    public function estimateByAddress($cartId, \Magento\Quote\Api\Data\EstimateAddressInterface $address)
    {
        /** @var $quoteIdMask QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        return $this->shippingMethodManagement->estimateByAddress($quoteIdMask->getQuoteId(), $address);
    }

    /**
     * @inheritdoc
     * @since 2.1.0
     */
    public function estimateByExtendedAddress($cartId, AddressInterface $address)
    {
        /** @var $quoteIdMask QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');

        return $this->getShipmentEstimationManagement()
            ->estimateByExtendedAddress((int) $quoteIdMask->getQuoteId(), $address);
    }

    /**
     * Get shipment estimation management service
     * @return ShipmentEstimationInterface
     * @deprecated 2.1.0
     * @since 2.1.0
     */
    private function getShipmentEstimationManagement()
    {
        if ($this->shipmentEstimationManagement === null) {
            $this->shipmentEstimationManagement = ObjectManager::getInstance()
                ->get(ShipmentEstimationInterface::class);
        }
        return $this->shipmentEstimationManagement;
    }
}
