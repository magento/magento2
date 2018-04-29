<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\Webapi;

use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Api\GuestCartItemRepositoryInterface;

/**
 * GuestCartItemRepository extension to save a cart item for a specific guest cart
 */
class SaveGuestCartItemWithCartId implements SaveGuestCartItemWithCartIdInterface
{
    /**
     * @var GuestCartItemRepositoryInterface
     */
    protected $guestCartItemRepo;

    /**
     * Constructor.
     *
     * @param GuestCartItemRepositoryInterface $guestCartItemRepo
     */
    public function __construct(GuestCartItemRepositoryInterface $guestCartItemRepo)
    {
        $this->guestCartItemRepo = $guestCartItemRepo;
    }

    /**
     * {@inheritdoc}
     */
    public function saveForCart($cartId, CartItemInterface $cartItem)
    {
        $cartItem->setQuoteId($cartId);

        return $this->guestCartItemRepo->save($cartItem);
    }
}
