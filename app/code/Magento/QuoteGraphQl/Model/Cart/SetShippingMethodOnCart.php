<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Cart;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\AddressFactory as QuoteAddressFactory;
use Magento\Quote\Model\ResourceModel\Quote\Address as QuoteAddressResource;
use Magento\Checkout\Model\ShippingInformationFactory;
use Magento\Checkout\Api\ShippingInformationManagementInterface;
use Magento\Checkout\Model\ShippingInformation;

/**
 * Class SetShippingMethodsOnCart
 *
 * Set shipping method for a specified shopping cart address
 */
class SetShippingMethodOnCart
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
     * @var ShippingInformationManagementInterface
     */
    private $shippingInformationManagement;

    /**
     * @param ShippingInformationManagementInterface $shippingInformationManagement
     * @param QuoteAddressFactory $quoteAddressFactory
     * @param QuoteAddressResource $quoteAddressResource
     * @param ShippingInformationFactory $shippingInformationFactory
     */
    public function __construct(
        ShippingInformationManagementInterface $shippingInformationManagement,
        QuoteAddressFactory $quoteAddressFactory,
        QuoteAddressResource $quoteAddressResource,
        ShippingInformationFactory $shippingInformationFactory
    ) {
        $this->shippingInformationManagement = $shippingInformationManagement;
        $this->quoteAddressResource = $quoteAddressResource;
        $this->quoteAddressFactory = $quoteAddressFactory;
        $this->shippingInformationFactory = $shippingInformationFactory;
    }

    /**
     * Sets shipping method for a specified shopping cart address
     *
     * @param Quote $cart
     * @param int $cartAddressId
     * @param string $carrierCode
     * @param string $methodCode
     * @throws GraphQlInputException
     * @throws GraphQlNoSuchEntityException
     */
    public function execute(Quote $cart, int $cartAddressId, string $carrierCode, string $methodCode): void
    {
        $quoteAddress = $this->quoteAddressFactory->create();
        $this->quoteAddressResource->load($quoteAddress, $cartAddressId);

        /** @var ShippingInformation $shippingInformation */
        $shippingInformation = $this->shippingInformationFactory->create();

        /* If the address is not a shipping address (but billing) the system will find the proper shipping address for
           the selected cart and set the information there (actual for single shipping address) */
        $shippingInformation->setShippingAddress($quoteAddress);
        $shippingInformation->setShippingCarrierCode($carrierCode);
        $shippingInformation->setShippingMethodCode($methodCode);

        try {
            $this->shippingInformationManagement->saveAddressInformation($cart->getId(), $shippingInformation);
        } catch (NoSuchEntityException $exception) {
            throw new GraphQlNoSuchEntityException(__($exception->getMessage()));
        } catch (StateException $exception) {
            throw new GraphQlInputException(__($exception->getMessage()));
        } catch (InputException $exception) {
            throw new GraphQlInputException(__($exception->getMessage()));
        }
    }
}
