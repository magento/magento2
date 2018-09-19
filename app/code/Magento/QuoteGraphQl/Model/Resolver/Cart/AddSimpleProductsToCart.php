<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Resolver\Cart;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Message\AbstractMessage;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;
use Magento\Quote\Model\Quote;
use Magento\QuoteGraphQl\Model\Cart\AddSimpleProductToCartProcessor;
use Magento\QuoteGraphQl\Model\Resolver\DataProvider\Cart\CartHydrator;

/**
 * Add simple product to cart GraphQl resolver
 * {@inheritdoc}
 */
class AddSimpleProductsToCart implements ResolverInterface
{
    /**
     * @var AddSimpleProductToCartProcessor
     */
    private $addSimpleProductToCartProcessor;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var MaskedQuoteIdToQuoteIdInterface
     */
    private $maskedQuoteIdToQuoteId;

    /**
     * @var CartHydrator
     */
    private $cartHydrator;

    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @var ValueFactory
     */
    private $valueFactory;

    /**
     * @var UserContextInterface
     */
    private $userContext;

    /**
     * @param AddSimpleProductToCartProcessor $addSimpleProductToCartProcessor
     * @param CartHydrator $cartHydrator
     * @param ArrayManager $arrayManager
     * @param MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId
     * @param CartRepositoryInterface $cartRepository
     * @param ValueFactory $valueFactory
     * @param UserContextInterface $userContext
     */
    public function __construct(
        AddSimpleProductToCartProcessor $addSimpleProductToCartProcessor,
        CartHydrator $cartHydrator,
        ArrayManager $arrayManager,
        MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId,
        CartRepositoryInterface $cartRepository,
        ValueFactory $valueFactory,
        UserContextInterface $userContext
    ) {
        $this->valueFactory = $valueFactory;
        $this->userContext = $userContext;
        $this->arrayManager = $arrayManager;
        $this->cartHydrator = $cartHydrator;
        $this->cartRepository = $cartRepository;
        $this->maskedQuoteIdToQuoteId = $maskedQuoteIdToQuoteId;
        $this->addSimpleProductToCartProcessor = $addSimpleProductToCartProcessor;
    }

    /**
     * Resolve adding simple product to cart for customers/guests
     * {@inheritdoc}
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null) : Value
    {
        $cartHash = $this->arrayManager->get('input/cart_id', $args);
        $cartItems = $this->arrayManager->get('input/cartItems', $args);

        if (!isset($cartHash)) {
            throw new GraphQlInputException(
                __('Missing key %1 in cart data', ['cart_id'])
            );
        }

        if (!isset($cartItems)) {
            throw new GraphQlInputException(
                __('Missing key %1 in cart data', ['cartItems'])
            );
        }

        $cart = $this->getCart((string) $cartHash);

        foreach ($cartItems as $cartItemData) {
            $sku = $this->arrayManager->get('details/sku', $cartItemData);

            $message = $this->addSimpleProductToCartProcessor->process($cart, $cartItemData);

            if (is_string($message)) {
                throw new GraphQlInputException(
                    __('%1: %2', $sku, $message)
                );
            }

            if ($cart->getData('has_error')) {
                throw new GraphQlInputException(
                    __('%1: %2', $sku, $this->getCartErrors($cart))
                );
            }
        }

        $this->cartRepository->save($cart);

        $result = function () use ($cart) {
            return [
                'cart' => $this->cartHydrator->hydrate($cart)
            ];
        };

        return $this->valueFactory->create($result);
    }

    /**
     * Collecting cart errors
     *
     * @param CartInterface|Quote $cart
     * @return string
     */
    private function getCartErrors($cart): string
    {
        $errorMessages = [];

        /** @var AbstractMessage $error */
        foreach ($cart->getErrors() as $error) {
            $errorMessages[] = $error->getText();
        }

        return implode(PHP_EOL, $errorMessages);
    }

    /**
     * Retrieving quote mode based on customer authorization
     *
     * @param string $cartHash
     * @return CartInterface|Quote
     * @throws NoSuchEntityException
     */
    private function getCart(string $cartHash): CartInterface
    {
        $cartId = $this->maskedQuoteIdToQuoteId->execute((string) $cartHash);

        return $this->cartRepository->get($cartId);
    }
}
