<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Resolver;

use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;
use Magento\QuoteGraphQl\Model\Cart\GetCartForCheckout;
use Magento\QuoteGraphQl\Model\Cart\PlaceOrder as PlaceOrderModel;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\SalesGraphQl\Model\Formatter\Order as OrderFormatter;

/**
 * Resolver for placing order after payment method has already been set
 */
class PlaceOrder implements ResolverInterface, ResetAfterRequestInterface
{
    /**#@+
     * Error message codes
     */
    private const ERROR_CART_NOT_FOUND = 'CART_NOT_FOUND';
    private const ERROR_CART_NOT_ACTIVE = 'CART_NOT_ACTIVE';
    private const ERROR_GUEST_EMAIL_MISSING = 'GUEST_EMAIL_MISSING';
    private const ERROR_UNABLE_TO_PLACE_ORDER = 'UNABLE_TO_PLACE_ORDER';
    private const ERROR_UNDEFINED = 'UNDEFINED';
    /**#@-*/

    /**
     * List of error messages and codes.
     */
    private const MESSAGE_CODES = [
        'Could not find a cart with ID' => self::ERROR_CART_NOT_FOUND,
        'The cart isn\'t active' => self::ERROR_CART_NOT_ACTIVE,
        'Guest email for cart is missing' => self::ERROR_GUEST_EMAIL_MISSING,
        'A server error stopped your order from being placed. Please try to place your order again' =>
            self::ERROR_UNABLE_TO_PLACE_ORDER,
        'Some addresses can\'t be used due to the configurations for specific countries' =>
            self::ERROR_UNABLE_TO_PLACE_ORDER,
        'The shipping method is missing. Select the shipping method and try again' =>
            self::ERROR_UNABLE_TO_PLACE_ORDER,
        'Please check the billing address information' => self::ERROR_UNABLE_TO_PLACE_ORDER,
        'Enter a valid payment method and try again' => self::ERROR_UNABLE_TO_PLACE_ORDER,
        'Some of the products are out of stock' => self::ERROR_UNABLE_TO_PLACE_ORDER,
    ];

    /**
     * @var \string[]
     */
    private $errors = [];

    /**
     * @param GetCartForCheckout $getCartForCheckout
     * @param PlaceOrderModel $placeOrder
     * @param OrderRepositoryInterface $orderRepository
     * @param OrderFormatter $orderFormatter
     */
    public function __construct(
        private readonly GetCartForCheckout $getCartForCheckout,
        private readonly PlaceOrderModel $placeOrder,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly OrderFormatter $orderFormatter
    ) {
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, ?array $value = null, ?array $args = null)
    {
        $this->errors = [];
        $order = null;
        if (empty($args['input']['cart_id'])) {
            throw new GraphQlInputException(__('Required parameter "cart_id" is missing'));
        }

        $maskedCartId = $args['input']['cart_id'];
        $userId = (int)$context->getUserId();
        $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();

        try {
            $cart = $this->getCartForCheckout->execute($maskedCartId, $userId, $storeId);
            $orderId = $this->placeOrder->execute($cart, $maskedCartId, $userId);
            $order = $this->orderRepository->get($orderId);
        } catch (NoSuchEntityException $exception) {
            $this->addError($exception->getMessage());
        } catch (GraphQlInputException $exception) {
            $this->addError($exception->getMessage());
        } catch (AuthorizationException $exception) {
            throw new GraphQlAuthorizationException(
                __($exception->getMessage())
            );
        } catch (LocalizedException $e) {
            $this->addError($e->getMessage());
        }
        if ($this->errors) {
            return [
                'errors' =>
                    $this->errors
            ];
        }
        return [
            'order' => [
                'order_number' => $order->getIncrementId(),
                // @deprecated The order_id field is deprecated, use order_number instead
                'order_id' => $order->getIncrementId(),
            ],
            'orderV2' => $this->orderFormatter->format($order),
            'errors' => []
        ];
    }

    /**
     * Add order line item error
     *
     * @param string $message
     * @return void
     */
    private function addError(string $message): void
    {
        $this->errors[] = [
            'message' => $message,
            'code' => $this->getErrorCode($message)
        ];
    }

    /**
     * Get message error code. Ad-hoc solution based on message parsing.
     *
     * @param string $message
     * @return string
     */
    private function getErrorCode(string $message): string
    {
        $code = self::ERROR_UNDEFINED;

        $matchedCodes = array_filter(
            self::MESSAGE_CODES,
            function ($key) use ($message) {
                return false !== strpos($message, $key);
            },
            ARRAY_FILTER_USE_KEY
        );

        if (!empty($matchedCodes)) {
            $code = current($matchedCodes);
        }

        return $code;
    }

    /**
     * @inheritDoc
     */
    public function _resetState(): void
    {
        $this->errors = [];
    }
}
