<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\Quote\ShippingAssignment;

use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\ShippingInterface;
use Magento\Quote\Model\ShippingFactory;
use Magento\Quote\Model\ShippingAddressManagement;
use Magento\Quote\Model\ShippingMethodManagement;
use Magento\Framework\App\ObjectManager;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class ShippingProcessor
{
    /**
     * @var ShippingFactory
     */
    private $shippingFactory;

    /**
     * @var ShippingAddressManagement
     */
    private $shippingAddressManagement;

    /**
     * @var ShippingMethodManagement
     */
    private $shippingMethodManagement;

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @param ShippingFactory $shippingFactory
     * @param ShippingAddressManagement $shippingAddressManagement
     * @param ShippingMethodManagement $shippingMethodManagement
     */
    public function __construct(
        ShippingFactory $shippingFactory,
        ShippingAddressManagement $shippingAddressManagement,
        ShippingMethodManagement $shippingMethodManagement
    ) {
        $this->shippingFactory = $shippingFactory;
        $this->shippingAddressManagement = $shippingAddressManagement;
        $this->shippingMethodManagement = $shippingMethodManagement;
    }

    /**
     * @param \Magento\Quote\Api\Data\AddressInterface $shippingAddress
     * @return \Magento\Quote\Api\Data\ShippingInterface
     */
    public function create(\Magento\Quote\Api\Data\AddressInterface $shippingAddress)
    {
        /** @var \Magento\Quote\Api\Data\ShippingInterface $shipping */
        $shipping = $this->shippingFactory->create();
        $shipping->setMethod($shippingAddress->getShippingMethod());
        $shipping->setAddress($shippingAddress);
        return $shipping;
    }

    /**
     * @param ShippingInterface $shipping
     * @param CartInterface $quote
     * @return void
     */
    public function save(ShippingInterface $shipping, CartInterface $quote)
    {
        $assignAddress = true;
        $shippingAddress = $shipping->getAddress();

        if ($shippingAddress->getCustomerAddressId()) {
            try {
                $this->getAddressRepository()->getById($shippingAddress->getCustomerAddressId());
            } catch (NoSuchEntityException $e) {
                //do not re-assign address if the original customer address does not exist
                $assignAddress = false;
            }
        }

        if ($assignAddress) {
            $this->shippingAddressManagement->assign($quote->getId(), $shippingAddress);
        }

        if (!empty($shipping->getMethod()) && $quote->getItemsCount() > 0) {
            $nameComponents = explode('_', $shipping->getMethod());
            $carrierCode = array_shift($nameComponents);
            // carrier method code can contains more one name component
            $methodCode = implode('_', $nameComponents);
            $this->shippingMethodManagement->apply($quote->getId(), $carrierCode, $methodCode);
        }
    }

    /**
     * Get Magento\Customer\Api\AddressRepositoryInterface instance
     *
     * @return AddressRepositoryInterface
     * @deprecated
     */
    private function getAddressRepository()
    {
        if ($this->addressRepository === null) {
            $this->addressRepository = ObjectManager::getInstance()->get(AddressRepositoryInterface::class);
        }

        return $this->addressRepository;
    }
}
