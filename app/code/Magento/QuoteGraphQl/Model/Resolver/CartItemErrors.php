<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\RuntimeException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Query\EnumLookup;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Model\Quote\Item;

/**
 * @inheritdoc
 */
class CartItemErrors implements ResolverInterface
{
    /**
     * Error code
     */
    private const ERROR_UNDEFINED = 0;

    /**
     * @var EnumLookup
     */
    private $enumLookup;

    /**
     * @param EnumLookup $enumLookup
     */
    public function __construct(
        EnumLookup $enumLookup
    ) {
        $this->enumLookup = $enumLookup;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }
        /** @var Item $cartItem */
        $cartItem = $value['model'];

        return $this->getItemErrors($cartItem);
    }

    /**
     * Get error messages for cart item
     *
     * @param Item $cartItem
     * @return string[]|null
     * @throws RuntimeException
     */
    private function getItemErrors(Item $cartItem): ?array
    {
        $hasError = (bool) $cartItem->getData('has_error');
        if (!$hasError) {
            return null;
        }

        $errors = [];
        foreach ($cartItem->getErrorInfos() as $error) {
            $errorType = $error['code'] ?? self::ERROR_UNDEFINED;
            $message = $error['message'] ?? $cartItem->getMessage();
            $errorEnumCode = $this->enumLookup->getEnumValueFromField(
                'CartItemErrorType',
                (string)$errorType
            );
            $errors[$message] = [
                'code' => $errorEnumCode,
                'message' => $message
            ];
        }

        return array_values($errors);
    }
}
