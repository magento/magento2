<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Resolver\ShippingMethod;

use Magento\Checkout\Api\ShippingInformationManagementInterface;
use Magento\Checkout\Model\ShippingInformation;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Quote\Model\Quote\AddressFactory as QuoteAddressFactory;
use Magento\Quote\Model\ResourceModel\Quote\Address as QuoteAddressResource;
use Magento\Checkout\Model\ShippingInformationFactory;
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;

/**
 * Class SetShippingMethodsOnCart
 *
 * Mutation resolver for setting shipping methods for shopping cart
 */
class SetShippingMethodsOnCart implements ResolverInterface
{
    /**
     * @var ShippingInformationFactory
     */
    private $shippingInformationFactory;

    /**
     * @var QuoteAddressFactory
     */
    private $quoteAddressFactory;

    /**
     * @var QuoteAddressResource
     */
    private $quoteAddressResource;

    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @var GetCartForUser
     */
    private $getCartForUser;

    /**
     * @var ShippingInformationManagementInterface
     */
    private $shippingInformationManagement;

    /**
     * SetShippingMethodsOnCart constructor.
     * @param ArrayManager $arrayManager
     * @param GetCartForUser $getCartForUser
     * @param ShippingInformationManagementInterface $shippingInformationManagement
     * @param QuoteAddressFactory $quoteAddressFactory
     * @param QuoteAddressResource $quoteAddressResource
     * @param ShippingInformationFactory $shippingInformationFactory
     */
    public function __construct(
        ArrayManager $arrayManager,
        GetCartForUser $getCartForUser,
        ShippingInformationManagementInterface $shippingInformationManagement,
        QuoteAddressFactory $quoteAddressFactory,
        QuoteAddressResource $quoteAddressResource,
        ShippingInformationFactory $shippingInformationFactory
    ) {
        $this->arrayManager = $arrayManager;
        $this->getCartForUser = $getCartForUser;
        $this->shippingInformationManagement = $shippingInformationManagement;
        $this->quoteAddressResource = $quoteAddressResource;
        $this->quoteAddressFactory = $quoteAddressFactory;
        $this->shippingInformationFactory = $shippingInformationFactory;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $shippingMethods = $this->arrayManager->get('input/shipping_methods', $args);
        $maskedCartId = $this->arrayManager->get('input/cart_id', $args);

        if (!$maskedCartId) {
            throw new GraphQlInputException(__('Required parameter "cart_id" is missing'));
        }
        if (!$shippingMethods) {
            throw new GraphQlInputException(__('Required parameter "shipping_methods" is missing'));
        }

        $shippingMethod = reset($shippingMethods);

        if (!$shippingMethod['cart_address_id']) {
            throw new GraphQlInputException(__('Required parameter "cart_address_id" is missing'));
        }
        if (!$shippingMethod['shipping_carrier_code']) {
            throw new GraphQlInputException(__('Required parameter "shipping_carrier_code" is missing'));
        }
        if (!$shippingMethod['shipping_method_code']) {
            throw new GraphQlInputException(__('Required parameter "shipping_method_code" is missing'));
        }

        $userId = $context->getUserId();
        $cart = $this->getCartForUser->execute((string) $maskedCartId, $userId);

        $quoteAddress = $this->quoteAddressFactory->create();
        $this->quoteAddressResource->load($quoteAddress, $shippingMethod['cart_address_id']);

        /** @var ShippingInformation $shippingInformation */
        $shippingInformation = $this->shippingInformationFactory->create();

        /* If the address is not a shipping address (but billing) the system will find the proper shipping address for
           the selected cart and set the information there (actual for single shipping address) */
        $shippingInformation->setShippingAddress($quoteAddress);
        $shippingInformation->setShippingCarrierCode($shippingMethod['shipping_carrier_code']);
        $shippingInformation->setShippingMethodCode($shippingMethod['shipping_method_code']);

        try {
            $this->shippingInformationManagement->saveAddressInformation($cart->getId(), $shippingInformation);
        } catch (NoSuchEntityException $exception) {
            throw new GraphQlNoSuchEntityException(__($exception->getMessage()));
        } catch (StateException $exception) {
            throw new GraphQlInputException(__($exception->getMessage()));
        } catch (InputException $exception) {
            throw new GraphQlInputException(__($exception->getMessage()));
        }

        return [
            'cart' => [
                'cart_id' => $maskedCartId,
                'model' => $cart
            ]
        ];
    }
}
