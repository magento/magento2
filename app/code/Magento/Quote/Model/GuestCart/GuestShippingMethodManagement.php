<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\Quote\Model\GuestCart;

use Magento\Framework\App\ObjectManager;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\GuestShipmentEstimationInterface;
use Magento\Quote\Api\ShipmentEstimationInterface;
use Magento\Quote\Api\ShippingMethodManagementInterface;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Model\GuestCart\GetGuestCart;

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
     * @var GetGuestCart|null
     */
    private $getGuestCart;

    /**
     * Constructs a shipping method read service object.
     *
     * @param ShippingMethodManagementInterface $shippingMethodManagement
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @param GetGuestCart|null $getGuestCart
     */
    public function __construct(
        ShippingMethodManagementInterface $shippingMethodManagement,
        QuoteIdMaskFactory $quoteIdMaskFactory,
        ?GetGuestCart $getGuestCart = null
    ) {
        $this->shippingMethodManagement = $shippingMethodManagement;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->getGuestCart = $getGuestCart ?? ObjectManager::getInstance()->get(GetGuestCart::class);
    }

    /**
     * @inheritDoc
     */
    public function get($cartId)
    {
        /** @var $quoteIdMask QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        $this->getGuestCart->execute($cartId, (int) $quoteIdMask->getQuoteId());
        return $this->shippingMethodManagement->get($quoteIdMask->getQuoteId());
    }

    /**
     * @inheritDoc
     */
    public function getList($cartId)
    {
        /** @var $quoteIdMask QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        $this->getGuestCart->execute($cartId, (int) $quoteIdMask->getQuoteId());
        return $this->shippingMethodManagement->getList($quoteIdMask->getQuoteId());
    }

    /**
     * @inheritDoc
     */
    public function set($cartId, $carrierCode, $methodCode)
    {
        /** @var $quoteIdMask QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        $this->getGuestCart->execute($cartId, (int) $quoteIdMask->getQuoteId());
        return $this->shippingMethodManagement->set($quoteIdMask->getQuoteId(), $carrierCode, $methodCode);
    }

    /**
     * @inheritDoc
     */
    public function estimateByAddress($cartId, \Magento\Quote\Api\Data\EstimateAddressInterface $address)
    {
        /** @var $quoteIdMask QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        $this->getGuestCart->execute($cartId, (int) $quoteIdMask->getQuoteId());
        return $this->shippingMethodManagement->estimateByAddress($quoteIdMask->getQuoteId(), $address);
    }

    /**
     * @inheritdoc
     */
    public function estimateByExtendedAddress($cartId, AddressInterface $address)
    {
        /** @var $quoteIdMask QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        $this->getGuestCart->execute($cartId, (int) $quoteIdMask->getQuoteId());

        return $this->getShipmentEstimationManagement()
            ->estimateByExtendedAddress((int) $quoteIdMask->getQuoteId(), $address);
    }

    /**
     * Get shipment estimation management service
     *
     * @return ShipmentEstimationInterface
     * @deprecated 100.0.7
     * @see getShipmentEstimationManagement
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
