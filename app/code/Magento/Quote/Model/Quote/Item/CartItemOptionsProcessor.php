<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Model\Quote\Item;

use Magento\Framework\DataObject;
use Magento\Quote\Api\Data\CartItemInterface;

/**
 * Cart item options processor
 *
 * @api
 */
class CartItemOptionsProcessor
{
    /**
     * @var CartItemProcessorInterface[]
     */
    private $cartItemProcessors = [];

    /**
     * @param CartItemProcessorsPool $cartItemProcessorsPool
     */
    public function __construct(CartItemProcessorsPool $cartItemProcessorsPool)
    {
        $this->cartItemProcessors = $cartItemProcessorsPool->getCartItemProcessors();
    }

    /**
     * @param string $productType
     * @param CartItemInterface $cartItem
     * @return DataObject|float
     */
    public function getBuyRequest($productType, CartItemInterface $cartItem)
    {
        $params = (isset($this->cartItemProcessors[$productType]))
            ? $this->cartItemProcessors[$productType]->convertToBuyRequest($cartItem)
            : null;

        $params = ($params === null) ? $cartItem->getQty() : $params->setData('qty', $cartItem->getQty());
        return $this->addCustomOptionsToBuyRequest($cartItem, $params);
    }

    /**
     * Add custom options to buy request.
     *
     * @param CartItemInterface $cartItem
     * @param DataObject|float $params
     * @return DataObject|float
     */
    private function addCustomOptionsToBuyRequest(CartItemInterface $cartItem, $params)
    {
        if (isset($this->cartItemProcessors['custom_options'])) {
            $buyRequestUpdate = $this->cartItemProcessors['custom_options']->convertToBuyRequest($cartItem);
            if (!$buyRequestUpdate) {
                return $params;
            }
            if ($params instanceof DataObject) {
                $buyRequestUpdate->addData($params->getData());
            } elseif (is_numeric($params)) {
                $buyRequestUpdate->setData('qty', $params);
            }
            return $buyRequestUpdate;
        }
        return $params;
    }

    /**
     * @param CartItemInterface $cartItem
     * @return CartItemInterface
     */
    public function applyCustomOptions(CartItemInterface $cartItem)
    {
        if (isset($this->cartItemProcessors['custom_options'])) {
            $cartItem = $this->cartItemProcessors['custom_options']->processOptions($cartItem);
        }
        return $cartItem;
    }

    /**
     * @param string $productType
     * @param CartItemInterface $cartItem
     * @return CartItemInterface
     */
    public function addProductOptions($productType, CartItemInterface $cartItem)
    {
        $cartItem = (isset($this->cartItemProcessors[$productType]))
            ? $this->cartItemProcessors[$productType]->processOptions($cartItem)
            : $cartItem;
        return $cartItem;
    }
}
