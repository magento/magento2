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
 * Update the specified cart item
 */
class UpdateCartItem implements AddCartItemInterface
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
    public function execute(CartItemInterface $cartItem)
    {
        return $this->quoteItemRepository->save($cartItem);
    }
}
