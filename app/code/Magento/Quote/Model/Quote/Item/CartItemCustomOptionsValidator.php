<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Quote\Model\Quote\Item;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\CartItemInterface;

class CartItemCustomOptionsValidator implements CartItemValidatorInterface
{
    /**
     * @param ProductRepositoryInterface $productRepository
     * @param CartItemValidatorResultInterfaceFactory $cartItemValidatorResultFactory
     */
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
        private readonly CartItemValidatorResultInterfaceFactory $cartItemValidatorResultFactory
    ) {
    }

    /**
     * @inheritDoc
     */
    public function validate(CartInterface $cart, CartItemInterface $cartItem): CartItemValidatorResultInterface
    {
        $errors = [];
        if (is_array($cartItem?->getProductOption()?->getExtensionAttributes()?->getCustomOptions())) {
            $customOptions = $cartItem->getProductOption()->getExtensionAttributes()->getCustomOptions();
            $productOptions = [];
            try {
                $product = $this->productRepository->get($cartItem->getSku(), false, $cart->getStoreId());
            } catch (NoSuchEntityException) {
                $product = null;
            }

            foreach ($product?->getHasOptions() ? $product->getOptions() : [] as $option) {
                $productOptions[$option->getOptionId()] = $option;
            }

            foreach ($customOptions as $option) {
                if (!isset($productOptions[$option->getOptionId()])) {
                    $errors[] = new Phrase(
                        'No such entity with %fieldName = %fieldValue',
                        [
                            'fieldName' => 'option_id',
                            'fieldValue' => $option->getOptionId()
                        ]
                    );
                }
            }
        }

        return $this->cartItemValidatorResultFactory->create(['errors' => $errors]);
    }
}
