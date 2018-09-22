<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Resolver\ShippingAddress;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\ShippingAddressManagementInterface;

/**
 * @inheritdoc
 */
class SetShippingAddressesOnCart implements ResolverInterface
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
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var MaskedQuoteIdToQuoteIdInterface
     */
    private $maskedQuoteIdToQuoteId;

    /**
     * @param ShippingAddressManagementInterface $shippingAddressManagement
     * @param AddressRepositoryInterface $addressRepository
     * @param Address $addressModel
     * @param DataObjectHelper $dataObjectHelper
     * @param MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId
     */
    public function __construct(
        ShippingAddressManagementInterface $shippingAddressManagement,
        AddressRepositoryInterface $addressRepository,
        Address $addressModel,
        DataObjectHelper $dataObjectHelper,
        MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId
    ) {
        $this->shippingAddressManagement = $shippingAddressManagement;
        $this->addressRepository = $addressRepository;
        $this->addressModel = $addressModel;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->maskedQuoteIdToQuoteId = $maskedQuoteIdToQuoteId;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($args['input']['cart_id'])) {
            throw new GraphQlInputException(__('Required parameter "cart_id" is missing'));
        }
        $maskedCartId = $args['input']['cart_id'];
        $cartId = $this->maskedQuoteIdToQuoteId->execute($maskedCartId);

        $customerAddressId = $args['input']['customer_address_id'] ?? 0;
        $address = $args['input']['address'] ?? null;
        $cartItems = $args['input']['cart_items'] ?? [];

        if (!$customerAddressId && !$address) {
            throw new GraphQlInputException(__('Query should contain either address id or address input.'));
        }

        if (!$cartItems) {
            if($customerAddressId) {
                $customerAddress = $this->addressRepository->getById($customerAddressId);
                $shippingAddress = $this->addressModel->importCustomerAddressData($customerAddress);
                $this->shippingAddressManagement->assign($cartId, $shippingAddress);
            } else {
                $shippingAddress = $this->addressModel->addData($address);
                $this->shippingAddressManagement->assign($cartId, $shippingAddress);
            }
        } else {
            //TODO: implement multi shipping address assign flow
        }

        return [];
    }
}
