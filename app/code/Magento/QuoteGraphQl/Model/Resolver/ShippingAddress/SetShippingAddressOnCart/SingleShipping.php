<?php
/**
 * @author Atwix Team
 * @copyright Copyright (c) 2018 Atwix (https://www.atwix.com/)
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Resolver\ShippingAddress\SetShippingAddressOnCart;

use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\ShippingAddressManagementInterface;
use Magento\Customer\Api\AddressRepositoryInterface;

class SingleShipping
{
    /**
     * @var ShippingAddressManagementInterface
     */
    private $shippingAddressManagement;

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var Address
     */
    private $addressModel;

    /**
     * @param ShippingAddressManagementInterface $shippingAddressManagement
     * @param AddressRepositoryInterface $addressRepository
     * @param Address $addressModel
     */
    public function __construct(
        ShippingAddressManagementInterface $shippingAddressManagement,
        AddressRepositoryInterface $addressRepository,
        Address $addressModel
    ) {
        $this->shippingAddressManagement = $shippingAddressManagement;
        $this->addressRepository = $addressRepository;
        $this->addressModel = $addressModel;
    }

    /**
     * @param int $cartId
     * @param int|null $customerAddressId
     * @param array|null $address
     * @return void
     */
    public function setAddress(int $cartId, ?int $customerAddressId, ?array $address): void
    {
        if($customerAddressId) {
            $customerAddress = $this->addressRepository->getById($customerAddressId);
            $shippingAddress = $this->addressModel->importCustomerAddressData($customerAddress);
        } else {
            $shippingAddress = $this->addressModel->addData($address);
        }

        $this->shippingAddressManagement->assign($cartId, $shippingAddress);
    }
}
