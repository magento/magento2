<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InstantPurchase\Model;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Model\Address;
use Magento\Customer\Model\AddressFactory;
use Magento\Quote\Api\Data\ShippingMethodInterfaceFactory;
use Magento\Vault\Api\PaymentTokenManagementInterface;

/**
 *  Create instances of instant purchase option based on raw data with loading of all required objects.
 */
class InstantPurchaseOptionLoadingFactory
{
    /**
     * @var InstantPurchaseOptionFactory
     */
    private $instantPurchaseOptionFactory;

    /**
     * @var PaymentTokenManagementInterface
     */
    private $paymentTokenManagement;

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var AddressFactory
     */
    private $addressFactory;

    /**
     * @var ShippingMethodInterfaceFactory
     */
    private $shippingMethodFactory;

    /**
     * InstantPurchaseOptionLoadingFactory constructor.
     * @param InstantPurchaseOptionFactory $instantPurchaseOptionFactory
     * @param PaymentTokenManagementInterface $paymentTokenManagement
     * @param AddressRepositoryInterface $addressRepository
     * @param AddressFactory $addressFactory
     * @param ShippingMethodInterfaceFactory $shippingMethodFactory
     */
    public function __construct(
        InstantPurchaseOptionFactory $instantPurchaseOptionFactory,
        PaymentTokenManagementInterface $paymentTokenManagement,
        AddressRepositoryInterface $addressRepository,
        AddressFactory $addressFactory,
        ShippingMethodInterfaceFactory $shippingMethodFactory
    ) {
        $this->instantPurchaseOptionFactory = $instantPurchaseOptionFactory;
        $this->paymentTokenManagement = $paymentTokenManagement;
        $this->addressRepository = $addressRepository;
        $this->addressFactory = $addressFactory;
        $this->shippingMethodFactory = $shippingMethodFactory;
    }

    /**
     * Loads entities and use them for instant purchase option creation.
     *
     * @param int $customerId
     * @param string $paymentTokenPublicHash
     * @param int $shippingAddressId
     * @param int $billingAddressId
     * @param string $carrierCode
     * @param string $shippingMethodCode
     * @return InstantPurchaseOption
     */
    public function create(
        int $customerId,
        string $paymentTokenPublicHash,
        int $shippingAddressId,
        int $billingAddressId,
        string $carrierCode,
        string $shippingMethodCode
    ): InstantPurchaseOption {
        $paymentToken = $this->paymentTokenManagement->getByPublicHash($paymentTokenPublicHash, $customerId);
        $shippingAddress = $this->getAddress($shippingAddressId);
        $billingAddress = $this->getAddress($billingAddressId);
        $shippingMethod = $this->shippingMethodFactory->create()
            ->setCarrierCode($carrierCode)
            ->setMethodCode($shippingMethodCode);

        return $this->instantPurchaseOptionFactory->create(
            $paymentToken,
            $shippingAddress,
            $billingAddress,
            $shippingMethod
        );
    }

    /**
     * Loads customer address model by identifier.
     *
     * @param $addressId
     * @return Address
     */
    private function getAddress($addressId): Address
    {
        $addressDataModel = $this->addressRepository->getById($addressId);
        $address = $this->addressFactory->create();
        $address->updateData($addressDataModel);
        return $address;
    }
}
