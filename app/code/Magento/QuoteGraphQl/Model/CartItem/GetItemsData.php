<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained from
 * Adobe.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\CartItem;

use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\Uid;
use Magento\Quote\Api\Data\CartItemInterface;

class GetItemsData
{
    /**
     * @param Uid $uidEncoder
     */
    public function __construct(
        private readonly Uid $uidEncoder,
    ) {
    }

    /**
     * Retrieve cart items data
     *
     * @param CartItemInterface[] $cartItems
     * @return array
     */
    public function execute(array $cartItems): array
    {
        $itemsData = [];
        foreach ($cartItems as $cartItem) {
            $product = $cartItem->getProduct();
            if ($product === null) {
                $itemsData[] = new GraphQlNoSuchEntityException(
                    __("The product that was requested doesn't exist. Verify the product and try again.")
                );
                continue;
            }
            $productData = $product->getData();
            $productData['model'] = $product;
            $productData['uid'] = $this->uidEncoder->encode((string) $product->getId());

            $itemsData[] = [
                'id' => $cartItem->getItemId(),
                'uid' => $this->uidEncoder->encode((string) $cartItem->getItemId()),
                'quantity' => $cartItem->getQty(),
                'product' => $productData,
                'model' => $cartItem,
            ];
        }
        return $itemsData;
    }
}
