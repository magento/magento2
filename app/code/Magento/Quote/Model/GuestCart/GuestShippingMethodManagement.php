<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Model\GuestCart;

use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\GuestShipmentEstimationInterface;
use Magento\Quote\Api\ShipmentEstimationInterface;
use Magento\Quote\Api\ShippingMethodManagementInterface;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteIdMaskFactory;

/**
 * Shipping method management class for guest carts.
 */
class GuestShippingMethodManagement implements
    \Magento\Quote\Api\GuestShippingMethodManagementInterface,
    \Magento\Quote\Model\GuestCart\GuestShippingMethodManagementInterface,
    GuestShipmentEstimationInterface
{
    /**
     * @var ShippingMethodManagementInterface
     */
    private $shippingMethodManagement;

    /**
     * @var QuoteIdMaskFactory
     */
    private $quoteIdMaskFactory;

    /**
     * @var ShipmentEstimationInterface
     */
    private $shipmentEstimationManagement;

    /**
     * Constructs a shipping method read service object.
     *
     * @param ShippingMethodManagementInterface $shippingMethodManagement
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @param ShipmentEstimationInterface $shipmentEstimationManagement
     */
    public function __construct(
        ShippingMethodManagementInterface $shippingMethodManagement,
        QuoteIdMaskFactory $quoteIdMaskFactory,
        ShipmentEstimationInterface $shipmentEstimationManagement
    ) {
        $this->shippingMethodManagement = $shippingMethodManagement;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->shipmentEstimationManagement = $shipmentEstimationManagement;
    }

    /**
     * {@inheritDoc}
     */
    public function get($cartId)
    {
        /** @var $quoteIdMask QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        return $this->shippingMethodManagement->get($quoteIdMask->getQuoteId());
    }

    /**
     * {@inheritDoc}
     */
    public function getList($cartId)
    {
        /** @var $quoteIdMask QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        return $this->shippingMethodManagement->getList($quoteIdMask->getQuoteId());
    }

    /**
     * {@inheritDoc}
     */
    public function set($cartId, $carrierCode, $methodCode)
    {
        /** @var $quoteIdMask QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        return $this->shippingMethodManagement->set($quoteIdMask->getQuoteId(), $carrierCode, $methodCode);
    }

    /**
     * {@inheritDoc}
     */
    public function estimateByAddress($cartId, \Magento\Quote\Api\Data\EstimateAddressInterface $address)
    {
        /** @var $quoteIdMask QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        return $this->shippingMethodManagement->estimateByAddress($quoteIdMask->getQuoteId(), $address);
    }

    /**
     * @inheritdoc
     */
    public function estimateByExtendedAddress($cartId, AddressInterface $address)
    {
        /** @var $quoteIdMask QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');

        return $this->shipmentEstimationManagement
            ->estimateByExtendedAddress((int)$quoteIdMask->getQuoteId(), $address);
    }
}
