<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\Quote\Item;

use Magento\Quote\Api\Data\CartItemInterface;

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
     * @return \Magento\Framework\DataObject|float
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
     * @param \Magento\Framework\DataObject|float $params
     * @return \Magento\Framework\DataObject|float
     */
    private function addCustomOptionsToBuyRequest(CartItemInterface $cartItem, $params)
    {
        if (isset($this->cartItemProcessors['custom_options'])) {
            $buyRequestUpdate = $this->cartItemProcessors['custom_options']->convertToBuyRequest($cartItem);
            if (!$buyRequestUpdate) {
                return $params;
            }
            if ($params instanceof \Magento\Framework\DataObject) {
                $buyRequestUpdate->addData($params->getData());
            } else if (is_numeric($params)) {
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
