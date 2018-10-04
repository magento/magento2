<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Resolver\ShippingAddress;

use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;
use Magento\Quote\Model\ShippingAddressManagementInterface;
use Magento\QuoteGraphQl\Model\Resolver\ShippingAddress\SetShippingAddressOnCart\MultiShipping;
use Magento\QuoteGraphQl\Model\Resolver\ShippingAddress\SetShippingAddressOnCart\SingleShipping;

/**
 * @inheritdoc
 */
class SetShippingAddressesOnCart implements ResolverInterface
{
    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var MaskedQuoteIdToQuoteIdInterface
     */
    private $maskedQuoteIdToQuoteId;

    /**
     * @var MultiShipping
     */
    private $multiShipping;

    /**
     * @var SingleShipping
     */
    private $singleShipping;

    /**
     * @var ShippingAddressManagementInterface
     */
    private $shippingAddressManagement;

    /**
     * @param DataObjectHelper $dataObjectHelper
     * @param MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId
     * @param MultiShipping $multiShipping
     * @param SingleShipping $singleShipping
     * @param ShippingAddressManagementInterface $shippingAddressManagement
     */
    public function __construct(
        DataObjectHelper $dataObjectHelper,
        MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId,
        MultiShipping $multiShipping,
        SingleShipping $singleShipping,
        ShippingAddressManagementInterface  $shippingAddressManagement
    ) {
        $this->dataObjectHelper = $dataObjectHelper;
        $this->maskedQuoteIdToQuoteId = $maskedQuoteIdToQuoteId;
        $this->multiShipping = $multiShipping;
        $this->singleShipping = $singleShipping;
        $this->shippingAddressManagement = $shippingAddressManagement;
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

        $customerAddressId = $args['input']['customer_address_id'] ?? null;
        $address = $args['input']['address'] ?? null;
        $cartItems = $args['input']['cart_items'] ?? [];

        if (!$customerAddressId && !$address) {
            throw new GraphQlInputException(__('Query should contain either address id or address input.'));
        }

        //TODO: how to determine whether is multi shipping or not
        if (!$cartItems) {
            //TODO: assign cart items
            $this->singleShipping->setAddress($cartId, $customerAddressId, $address);
        } else {
            $this->multiShipping->setAddress($cartId, $cartItems, $customerAddressId, $address);
        }

        //TODO: implement Cart object in the separate resolver
        $shippingAddress = $this->shippingAddressManagement->get($cartId);
        return [
            'cart' => [
                'applied_coupon' => [
                    'code' => ''
                ],
                'addresses' => [[
                    'firstname' => $shippingAddress->getFirstname(),
                    'lastname' => $shippingAddress->getLastname(),
                    'company' => $shippingAddress->getCompany(),
                    'street' => $shippingAddress->getStreet(),
                    'city' => $shippingAddress->getCity(),
                    'region' => [
                        'code' => $shippingAddress->getRegionCode(),
                        'label' => $shippingAddress->getRegion()
                    ],
                    'country' => [
                        'code' => $shippingAddress->getCountryId(),
                        'label' => ''
                    ],
                    'postcode' => $shippingAddress->getPostcode(),
                    'telephone' => $shippingAddress->getTelephone(),
                    'address_type' => 'SHIPPING',
                    'selected_shipping_method' => [
                        'code' => 'test',
                        'label' => 'test',
                        'free_shipping' => 'test',
                        'error_message' => 'test'
                    ],
                    'available_shipping_methods' => [[
                        'code' => 'test',
                        'label' => 'test',
                        'free_shipping' => 'test',
                        'error_message' => 'test'
                    ]],
                    'items_weight' => [0],
                    'customer_notes' => $shippingAddress->getLastname(),
                    'cart_items' => [[
                        'cart_item_id' => '',
                            'quantity' => 0
                    ]],
                    'applied_coupon' => [
                        'code' => ''
                    ]
                ]]
            ]
        ];
    }
}
