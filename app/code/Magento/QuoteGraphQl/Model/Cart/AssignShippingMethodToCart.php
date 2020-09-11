<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Cart;

use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Checkout\Api\Data\ShippingInformationInterfaceFactory;
use Magento\Checkout\Api\ShippingInformationManagementInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\CartInterface;

/**
 * Assign shipping method to cart
 */
class AssignShippingMethodToCart
{
    /**
     * @var ShippingInformationInterfaceFactory
     */
    private $shippingInformationFactory;

    /**
     * @var ShippingInformationManagementInterface
     */
    private $shippingInformationManagement;

    /**
     * @param ShippingInformationInterfaceFactory $shippingInformationFactory
     * @param ShippingInformationManagementInterface $shippingInformationManagement
     */
    public function __construct(
        ShippingInformationInterfaceFactory $shippingInformationFactory,
        ShippingInformationManagementInterface $shippingInformationManagement
    ) {
        $this->shippingInformationFactory = $shippingInformationFactory;
        $this->shippingInformationManagement = $shippingInformationManagement;
    }

    /**
     * Assign shipping method to cart
     *
     * @param CartInterface $cart
     * @param AddressInterface $quoteAddress
     * @param string $carrierCode
     * @param string $methodCode
     * @throws GraphQlInputException
     * @throws GraphQlNoSuchEntityException
     */
    public function execute(
        CartInterface $cart,
        AddressInterface $quoteAddress,
        string $carrierCode,
        string $methodCode
    ): void {
        /** @var ShippingInformationInterface $shippingInformation */
        $shippingInformation = $this->shippingInformationFactory->create([
            'data' => [
                /* If the address is not a shipping address (but billing) the system will find the proper shipping
                   address for the selected cart and set the information there (actual for single shipping address) */
                ShippingInformationInterface::SHIPPING_ADDRESS => $quoteAddress,
                ShippingInformationInterface::SHIPPING_CARRIER_CODE => $carrierCode,
                ShippingInformationInterface::SHIPPING_METHOD_CODE => $methodCode,
            ],
        ]);

        try {
            $this->shippingInformationManagement->saveAddressInformation($cart->getId(), $shippingInformation);
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
        } catch (LocalizedException $e) {
            throw new GraphQlInputException(__($e->getMessage()), $e);
        }
    }
}
