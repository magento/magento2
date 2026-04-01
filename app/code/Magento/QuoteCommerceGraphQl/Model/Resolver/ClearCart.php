<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\QuoteCommerceGraphQl\Model\Resolver;

use Exception;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\QuoteCommerceGraphQl\Model\Cart\ClearCartItems;
use Magento\QuoteCommerceGraphQl\Model\Cart\ClearCartError;

/**
 * Clear Items from Cart.
 */
class ClearCart implements ResolverInterface
{
    /**
     * @var ClearCartItems
     */
    private $clearCartItems;

    /**
     * @var ClearCartError
     */
    private $clearCartError;

    /**
     * @param ClearCartError $clearCartError
     * @param ClearCartItems $clearCartItems
     */
    public function __construct(
        ClearCartError $clearCartError,
        ClearCartItems $clearCartItems
    ) {
        $this->clearCartError = $clearCartError;
        $this->clearCartItems = $clearCartItems;
    }

    /**
     * Clear all items from cart
     *
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array[]
     * @throws GraphQlInputException
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null
    ): array {
        if (empty($args['input']['uid'])) {
            throw new GraphQlInputException(__('Required parameter "uid" is missing.'));
        }
        try {
            $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();
            $cart = $this->clearCartItems
                ->execute($args['input']['uid'], $context->getUserId(), $storeId);
        } catch (LocalizedException $e) {
            return $this->processException($e, $e->getRawMessage());
        } catch (Exception $e) {
            $message = "Could not clear the cart.";
            return $this->processException(new Exception($message), $message);
        }

        return [
            'cart' => [
                'model' => $cart,
            ],
        ];
    }

    /**
     * Process exception.
     *
     * @param Exception $exception
     * @param string $message
     * @return array[]
     */
    private function processException(Exception $exception, string $message):array
    {
        return [
            'errors' => [
                [
                    'type' => $this->clearCartError->getErrorCode($message),
                    'message' => $exception->getMessage()
                ]
            ]
        ];
    }
}
