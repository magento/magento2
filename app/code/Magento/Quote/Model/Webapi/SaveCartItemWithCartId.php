<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\Webapi;

use Magento\Quote\Api\CartItemRepositoryInterface;
use Magento\Quote\Api\Data\CartItemInterface;

/**
 * CartItemRepository extension to save a cart item for a specific guest cart
 */
class SaveCartItemWithCartId implements SaveCartItemWithCartIdInterface
{
    /**
     * @var CartItemRepositoryInterface
     */
    private $cartItemRepository;

    /**
     * Constructor.
     *
     * @param CartItemRepositoryInterface $cartItemRepository
     */
    public function __construct(CartItemRepositoryInterface $cartItemRepository)
    {
        $this->cartItemRepository = $cartItemRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function saveForCart($cartId, CartItemInterface $cartItem)
    {
        $cartItem->setQuoteId($cartId);

        return $this->cartItemRepository->save($cartItem);
    }
}
