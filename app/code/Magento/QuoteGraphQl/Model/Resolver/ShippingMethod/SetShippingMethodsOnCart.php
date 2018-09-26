<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Resolver\ShippingMethod;

use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;

class SetShippingMethodsOnCart implements ResolverInterface
{
    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var MaskedQuoteIdToQuoteIdInterface
     */
    private $maskedQuoteIdToQuoteId;

    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * SetShippingMethodsOnCart constructor.
     * @param ArrayManager $arrayManager
     * @param MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId
     */
    public function __construct(
        ArrayManager $arrayManager,
        MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId,
        CartRepositoryInterface $cartRepository
    ) {
        $this->arrayManager = $arrayManager;
        $this->maskedQuoteIdToQuoteId = $maskedQuoteIdToQuoteId;
        $this->cartRepository = $cartRepository;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $shippingMethods = $this->arrayManager->get('input/shipping_methods', $args);
        $maskedCartId = $this->arrayManager->get('input/cart_id', $args);

        if (!$maskedCartId) {
            // TODO: throw an exception
        }

        if (!$shippingMethods) {
            // TODO: throw an exception?
        }

        foreach ($shippingMethods as $shippingMethod) {
            if (empty($shippingMethod['cart_address_id'])) {
                // TODO: throw input exception
            }

            if (empty($shippingMethod['shipping_method_code'])) {
                // TODO: throw input exception
            }

            // TODO: move to a separate class
            // TODO: check current customer can apply operations on specified cart
        }

        $quoteId = $this->maskedQuoteIdToQuoteId->execute($maskedCartId);
        $quote = $this->cartRepository->get($quoteId); // TODO: catch no such entity exception
        $this->setShippingMethods($shippingMethods, $quote);

        $quote->collectTotals();
        $quote->save();
        //$this->cartRepository->save($quote);

        return 'Success!';
    }

    private function setShippingMethods($shippingMethods, CartInterface $quote)
    {
        $addresses = $quote->getAllShippingAddresses();
        /** @var  \Magento\Quote\Model\Quote\Address $address */
        foreach ($addresses as $address) {
            $addressId = $address->getId();
            $shippingMethodForAddress = array_search($addressId, array_column($shippingMethods, 'cart_address_id'));
            if ($shippingMethodForAddress !== false) {
                $address->setShippingMethod($shippingMethods[$shippingMethodForAddress]['shipping_method_code']);
//                $address->setCollectShippingRates(1);
                $address->save();
            }
        }
        // TODO: make sure that shipping method is assigned for all addresses
    }
}