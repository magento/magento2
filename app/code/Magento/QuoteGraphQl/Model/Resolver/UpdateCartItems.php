<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;
use Magento\QuoteGraphQl\Model\CartItem\DataProvider\UpdateCartItems as  UpdateCartItemsProvider;
use Magento\Framework\GraphQl\Query\Resolver\ArgumentsProcessorInterface;

/**
 * @inheritdoc
 */
class UpdateCartItems implements ResolverInterface
{
    /**
     * Undefined error code
     */
    private const CODE_UNDEFINED = 'UNDEFINED';

    /**
     * @param GetCartForUser $getCartForUser
     * @param CartRepositoryInterface $cartRepository
     * @param UpdateCartItemsProvider $updateCartItems
     * @param ArgumentsProcessorInterface $argsSelection
     * @param array $messageCodesMapper
     */
    public function __construct(
        private readonly GetCartForUser $getCartForUser,
        private readonly CartRepositoryInterface $cartRepository,
        private readonly UpdateCartItemsProvider $updateCartItems,
        private readonly ArgumentsProcessorInterface $argsSelection,
        private readonly array $messageCodesMapper,
    ) {
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, ?array $value = null, ?array $args = null)
    {
        $processedArgs = $this->argsSelection->process($info->fieldName, $args);

        if (empty($processedArgs['input']['cart_id'])) {
            throw new GraphQlInputException(__('Required parameter "cart_id" is missing.'));
        }

        $maskedCartId = $processedArgs['input']['cart_id'];

        $errors = [];
        if (empty($processedArgs['input']['cart_items'])
            || !is_array($processedArgs['input']['cart_items'])
        ) {
            $message = 'Required parameter "cart_items" is missing.';
            $errors[] = [
                'message' => __($message),
                'code' => $this->getErrorCode($message)
            ];
        }

        $cartItems = $processedArgs['input']['cart_items'];
        $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();
        $cart = $this->getCartForUser->execute($maskedCartId, $context->getUserId(), $storeId);

        try {
            $this->updateCartItems->processCartItems($cart, $cartItems);
            $this->cartRepository->save(
                $this->cartRepository->get((int)$cart->getId())
            );
        } catch (NoSuchEntityException | LocalizedException $e) {
            $message = (str_contains($e->getMessage(), 'The requested qty is not available'))
                ? 'The requested qty. is not available'
                : $e->getMessage();
            $errors[] = [
                'message' => __($message),
                'code' => $this->getErrorCode($e->getMessage())
            ];
        }

        return [
            'cart' => [
                'model' => $cart,
            ],
            'errors' => $errors,
        ];
    }

    /**
     * Returns error code based on error message
     *
     * @param string $message
     * @return string
     */
    private function getErrorCode(string $message): string
    {
        $message = preg_replace('/\d+/', '%s', $message);
        foreach ($this->messageCodesMapper as $key => $code) {
            if (str_contains($message, $key)) {
                return $code;
            }
        }
        return self::CODE_UNDEFINED;
    }
}
