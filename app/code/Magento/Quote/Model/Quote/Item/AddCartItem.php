<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Model\Quote\Item;

use Magento\Quote\Api\AddCartItemInterface;
use Magento\Quote\Api\Data\CartItemInterface;

/**
 * Add the specified cart item
 */
class AddCartItem implements AddCartItemInterface
{
    /**
     * @var Repository
     */
    private $quoteItemRepository;

    /**
     * @param Repository $quoteItemRepository
     */
    public function __construct(Repository $quoteItemRepository)
    {
        $this->quoteItemRepository = $quoteItemRepository;
    }

    /**
     * @inheritDoc
     */
    public function execute(CartItemInterface $cartItem): CartItemInterface
    {
        if (isset($cartItem[CartItemInterface::KEY_ITEM_ID])) {
            unset($cartItem[CartItemInterface::KEY_ITEM_ID]);
        }

        return $this->quoteItemRepository->save($cartItem);
    }
}
