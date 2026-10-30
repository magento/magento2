<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Quote\Model\Quote\Item;

use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\CartItemInterface;

class CartItemValidatorChain implements CartItemValidatorInterface
{
    /**
     * @param CartItemValidatorResultInterfaceFactory $cartItemValidatorResultFactory
     * @param CartItemValidatorInterface[] $validators
     * @param bool $breakChainOnFailure
     */
    public function __construct(
        private readonly CartItemValidatorResultInterfaceFactory $cartItemValidatorResultFactory,
        private readonly array $validators = [],
        private readonly bool $breakChainOnFailure = false
    ) {
        // Ensure that all validators are instances of CartItemValidatorInterface
        array_map(fn (CartItemValidatorInterface $validator) => $validator, $this->validators);
    }

    /**
     * @inheritDoc
     */
    public function validate(CartInterface $cart, CartItemInterface $cartItem): CartItemValidatorResultInterface
    {
        $errors = [];
        foreach ($this->validators as $validator) {
            $errors = [...$errors, ...$validator->validate($cart, $cartItem)->getErrors()];
            if ($this->breakChainOnFailure && !empty($errors)) {
                break;
            }
        }

        return $this->cartItemValidatorResultFactory->create(['errors' => $errors]);
    }
}
